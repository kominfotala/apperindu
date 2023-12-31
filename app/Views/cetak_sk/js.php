<script src="<?= base_url() ?>assets/plugins/datatable/js/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>assets/plugins/datatable/js/dataTables.bootstrap5.min.js"></script>
<script src="<?= base_url() ?>assets/plugins/select2/js/select2.min.js"></script>
<!-- <script src="assets/js/form-select2.js"></script> -->
<script>
$(document).ready(function() {
    $('.hide').hide();
    $('.filter-select, .single-select').select2({
        theme: 'bootstrap4',
        width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' :
            'style',
        placeholder: $(this).data('placeholder'),
        allowClear: Boolean($(this).data('allow-clear')),
    });

    if (!$('#tblizinpendaftaran_id').val()) {
        datatable(true);
    }


});


function datatable(bool) {
    let data = {
        tblizin_id: $('#id_izin').val(),
        tblizinpermohonan_id: $('#id_permohonan').val(),
        'str': 'cetak_sk',
        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
    }
    $('.table').DataTable({
        responsive: false,
        autoWidth: false,
        searching: true,
        info: true,
        paginate: true,
        bDestroy: true,
        ordering: false,
        language: {
            lengthMenu: "_MENU_  data per halaman",
            zeroRecords: "Data tidak tersedia",
            search: "Pencarian ",
            info: "Menampilkan halaman _PAGE_ dari _PAGES_ halaman",
            infoEmpty: "Data tidak tersedia",
            infoFiltered: "(hasil pencarian/filter dari _MAX_ total data)",
            paginate: {
                "previous": "<",
                "next": ">",
            }
        },
        stateSave: bool,
        processing: true, //Feature control the processing indicator.
        serverSide: true, //Feature control DataTables' server-side processing mode.
        order: [], //Initial no order.

        // Load data for the table's content from an Ajax source
        ajax: {
            url: "<?php echo site_url($url . '/get_data') ?>",
            data: data,
            type: "POST",

        },


        //Set column definition initialisation properties.
        columnDefs: [{
            targets: [0], //first column / numbering column
            orderable: false, //set not orderable
        }, ],
    });
}


function hapus(id) {
    $('.form-delete').trigger('reset');
    $('#form-delete-modal').modal('show');
    $('#id-delete').val(id);
}



function downloadFile(url, name) {

    var xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);
    xhr.responseType = 'blob';

    xhr.onload = function() {
        if (xhr.status === 200) {
            // Membuat URL objek dari respon blob
            var url = window.URL.createObjectURL(xhr.response);

            // Membuat elemen anchor untuk mengunduh file
            var link = document.createElement('a');
            link.href = url;
            link.download = name;
            link.style.display = 'none';
            document.body.appendChild(link);

            // Mengklik elemen anchor untuk memulai unduhan
            link.click();

            // Menghapus elemen anchor dari dokumen
            document.body.removeChild(link);

            // Merevokasi URL objek setelah selesai diunduh
            window.URL.revokeObjectURL(url);
        }
    };

    xhr.send();
}

function permohonan_dinamis(id, el) {

    $(el).find('option').not(':first').remove();
    $.ajax({
        url: "<?php echo site_url('pendaftaran/get_permohonan_by_id_izin_json') ?>", // Ganti dengan URL yang sesuai
        type: 'POST',
        data: {
            id_izin: id,
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        },
        dataType: 'json',
        success: function(response) {
            console.log(response);
            if (response.status) {
                // Tambahkan opsi subkategori berdasarkan respons dari server
                $.each(response.data, function(key, value) {

                    $(el).append('<option value="' + value
                        .tblizinpermohonan_id + '">' + value.tblizinpermohonan_nama +
                        '</option>');
                });
            }
        },
        error: function(xhr, status, error) {
            console.log(xhr.responseText);
        }
    });
}



function loading_button(status) {
    if (status) {
        $('.cetak').prop('disabled', true);
        html =
            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Tunggu sebentar...'
        $('.cetak').html(html);
    } else {

        $('.cetak').prop('disabled', false);
        html = 'Cetak'
        $('.cetak').html(html);

    }
}

function loading_button2(status) {
    if (status) {
        $('.simpan').prop('disabled', true);
        html =
            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Tunggu sebentar...'
        $('.simpan').html(html);
    } else {

        $('.simpan').prop('disabled', false);
        html = 'Simpan'
        $('.simpan').html(html);

    }
}

// Listen for the form's submit event
$('.form').submit(function(event) {
    event.preventDefault(); // Prevent the default form submission

    // Get the form data
    const formData = $(this).serialize();


    $.ajax({
        data: formData,
        type: "POST",
        dataType: 'json',
        url: "<?= site_url($path . '/form') ?>",
        beforeSend: function() {
            // Menampilkan elemen loading
            loading_button(true);
        },
        success: function(response) {
            if (response.status) {

                if ($('#download').prop('checked')) {
                    downloadFile(response.url_file, response.name_file);
                }

                success(response.msg);
                loading_button(false);
                setTimeout(function() {
                    reviewCetak(response.path)
                }, 1000);

                // setTimeout(function() {
                //     window.location.href = response.url;
                // }, 1000);

            } else {
                error(response.msg);
                loading_button(false);
            }

        },
        error: function() {
            // Menyembunyikan elemen loading jika terjadi kesalahan
            error('Terjadi kesalahan');
            // setTimeout(function() {
            //     history.back()
            // }, 1000);
        }
    });




});


$('.form2').submit(function(event) {
    event.preventDefault(); // Prevent the default form submission

    // Get the form data
    const formData = $(this).serialize();


    $.ajax({
        data: formData,
        type: "POST",
        dataType: 'json',
        url: "<?= site_url($path . '/form_simpan') ?>",
        beforeSend: function() {
            // Menampilkan elemen loading
            loading_button2(true);
        },
        success: function(response) {
            if (response.status) {


                success(response.msg);
                loading_button2(false);

                setTimeout(function() {
                    window.location.href = response.url;
                }, 1000);

            } else {
                error(response.msg);
                loading_button2(false);
            }

        },
        error: function() {
            // Menyembunyikan elemen loading jika terjadi kesalahan
            error('Terjadi kesalahan');
            loading_button2(false);
        }
    });




});

$('#no_izin').keyup(function() {

    let formData = {
        table: $('#table').val(),
        no_izin: $(this).val(),
        tblizinpendaftaran_id: $('#tblizinpendaftaran_id').val(),
        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
    };



    if ($(this).val() == '') {
        $('#no_izin').removeClass("is-valid");
        $('#no_izin').addClass("is-invalid");


        // Periksa apakah elemen div dengan kelas "valid-feedback" ada dan hapus jika ada
        if ($('#no_izin').next().hasClass("valid-feedback")) {
            $('#no_izin').next().remove();
        }

        // Tambahkan elemen div dengan kelas "invalid-feedback" jika belum ada
        if (!$('#no_izin').next().hasClass("invalid-feedback")) {
            $('#no_izin').after('<div class="invalid-feedback">Nomor tidak boleh kosong</div>');
        }

        $('.cetak').prop('disabled', true);
        return false;
    }


    $.ajax({
        data: formData,
        type: "POST",
        dataType: 'json',
        url: "<?= site_url($path . '/validasi_no_izin') ?>",
        beforeSend: function() {
            loading_button(true);
        },
        success: function(response) {

            const dynamicText = response.msg;
            if (response.status) {
                $('#no_izin').removeClass("is-invalid");
                $('#no_izin').addClass("is-valid");


                // Periksa apakah elemen div dengan kelas "invalid-feedback" ada dan hapus jika ada
                if ($('#no_izin').next().hasClass("invalid-feedback")) {
                    $('#no_izin').next().remove();
                }

                // Tambahkan elemen div dengan kelas "valid-feedback" jika belum ada
                if (!$('#no_izin').next().hasClass("valid-feedback")) {
                    $('#no_izin').after(`<div class="valid-feedback">${dynamicText}</div>`);
                }

                loading_button(false);
            } else {
                $('#no_izin').removeClass("is-valid");
                $('#no_izin').addClass("is-invalid");


                // Periksa apakah elemen div dengan kelas "valid-feedback" ada dan hapus jika ada
                if ($('#no_izin').next().hasClass("valid-feedback")) {
                    $('#no_izin').next().remove();
                }

                // Tambahkan elemen div dengan kelas "invalid-feedback" jika belum ada
                if (!$('#no_izin').next().hasClass("invalid-feedback")) {
                    $('#no_izin').after(`<div class="invalid-feedback">${dynamicText}</div>`);
                }

                loading_button(false);
                $('.cetak').prop('disabled', true);
            }



        },
        error: function() {

            error('Terjadi kesalahan');
            loading_button(false);
        }
    });
});




$('.filter-select').change(function() {
    datatable(false);
})

$('#id_izin').change(function() {
    permohonan_dinamis($(this).val(), '#id_permohonan');
});

function update(id) {

    $('.form-update').trigger('reset');
    $('#form-modal').modal('show');
    $('#id-update').val(id);

    var input = ['tblizinpendaftaran_idpemohon', 'tblizinpendaftaran_namapemohon', 'tblizinpendaftaran_almtpemohon',
        'tblizinpendaftaran_npwp', 'tblizinpendaftaran_telponpemohon', 'tblizin_id', 'tblizinpendaftaran_usaha',
        'tblkecamatan_id', 'tblizinpendaftaran_lokasiizin', 'tblizinpendaftaran_keterangan', 'tblpemohon_id'
    ];

    $.ajax({
        data: {
            id: id,
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        },
        type: "POST",
        dataType: 'json',
        url: "<?= site_url('pendaftaran/get_row') ?>",
        success: function(response) {
            if (response.status) {
                let data = response.data;
                input.forEach(element => {
                    $('#' + element).val(data[element]);
                });

            }

            ;

        }
    });

}

function formatRupiah(angka) {
    var reverse = angka.toString().split('').reverse().join('');
    var ribuan = reverse.match(/\d{1,3}/g);
    var hasil = ribuan.join('.').split('').reverse().join('');
    return 'Rp. ' + hasil + ',-'
}

// Event handler saat input berubah
$('#modal_usaha').on('input', function() {
    // Ambil nilai input
    var nilaiInput = $(this).val();

    // Hapus karakter selain digit
    var nilaiClean = nilaiInput.replace(/[^\d]/g, '');

    // Format Rupiah
    var nilaiFormatted = formatRupiah(nilaiClean);

    // Set nilai kembali ke input
    $(this).val(nilaiFormatted);
});
</script>