var table;

$(document).ready(function () {

    ajaxcsrf();


    table = $("#detail_hasil").DataTable({
        initComplete: function () {
            var api = this.api();
            $('#detail_hasil_filter input')
                .off('.DT')
                .on('keyup.DT', function (e) {
                    api.search(this.value).draw();
                });
        },
        "lengthMenu": [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, "All"]
        ],
        dom:
            "<'row'<'col-sm-3'l><'col-sm-6 text-center'B><'col-sm-3'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",

        buttons: [
            {
                extend: "excel",
                exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
            },
            {
                extend: "pdf",
                exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
            }
        ],
        oLanguage: {
            sProcessing: "loading..."
        },
        processing: true,
        serverSide: true,
        ajax: {
            "url": base_url + "hasilujian/NilaiMhs/" + id,
            "type": "POST",
        },
        columns: [
            {
                "data": "id",
                "orderable": false,
                "searchable": false
            },
            { "data": 'nim' },
            { "data": 'nama' },
            { "data": 'nama_kelas' },
            { "data": 'nama_jurusan' },
            { "data": 'jml_benar' },
            { "data": 'nilai' },
            { "data": 'tgl_mulai' },
        ],
        columnDefs: [
            { "targets": 10, searchable: false, "data": 'list_jawaban' },
            {
                "targets": 9,
                searchable: false,
                // "data": "idmahasiswa",
                data: {
                    idujian: "idujian",
                    idmahasiswa: "idmahasiswa",
                    tgl_selesai: "tgl_selesai",
                    status: "status",
                    id: "id"
                },
                "render": function (data, type, row, meta) {
                    const d = new Date(data.tgl_selesai);
                    const today = new Date();
                    var bool = (d > today);//tanggal selesai kurang dari t
                    if (data.status == "N") {
                        return `<div class="text-center">-</div>`;
                    }
                    else if (data.status == "Y" && bool == true) {
                        return `<button data-id="${data.idmahasiswa}"  data-ujian="${data.idujian}" onclick="return confirm('Yakin Hapus?');" type="button" class="btn btn-xs btn-primary btn-reset-ujian">
                    reset ujian
                </button>`;
                    }
                    else {
                        return `<button data-id="${data.idmahasiswa}"  data-ujian="${data.id}"  type="button" class="btn btn-xs btn-success btn-akumulasikan">
                        Akumulasikan
                    </button>`;
                    }
                }
            },
            {
                "targets": 8,
                searchable: false,
                data: {
                    tgl_selesai: "tgl_selesai",
                    status: "status"
                },
                "render": function (data, type, row, meta) {
                    const d = new Date(data.tgl_selesai);
                    const today = new Date();
                    var bool = (d > today);//tanggal selesai kurang dari t
                    if (data.status == "N") {
                        return `<div class="text-center">
                        <span class="badge bg-green">Selesai</span>
                        </div>`;
                    }
                    else if (data.status == "Y" && bool == true) {
                        return `<div class="text-center">
                        <span class="badge bg-yellow">Berlangsung</span>
                        </div>`;
                    }
                    else {
                        return `<div class="text-center">
                        <span class="badge bg-red">Tidak Submit</span>
                        </div>`;
                    }

                }
            }
        ],
        order: [
            [1, 'asc']
        ],
        rowId: function (a) {
            return a;
        },
        rowCallback: function (row, data, iDisplayIndex) {
            var info = this.fnPagingInfo();
            var page = info.iPage;
            var length = info.iLength;
            var index = page * length + (iDisplayIndex + 1);
            $('td:eq(0)', row).html(index);
        }
    });

    // document.getElementsByClassName("nilai_essay").addEventListener("change", myFunction);

    // function myFunction() {
    //     var x = document.getElementsByClassName("nilai_essay");
    //     alert(x.value);
    // }

    $("#detail_hasil").on("click", ".btn-reset-ujian", function () {
        let id = $(this).data("id");
        let idujian = $(this).data("ujian");
        // console.log(id);

        $.ajax({
            url: base_url + "hasilujian/resetujian",
            data: "siswa=" + id + "&ujian=" + idujian,
            type: "GET",
            success: function (response) {
                if (response.status == true) {
                    Swal({
                        title: "Berhasil",
                        text: "Data Ujian Berhasil Dihapus",
                        type: "success"
                    });
                } else {
                    Swal({
                        title: "Gagal",
                        text: "Data Ujian Gagal Dihapus",
                        type: "error"
                    });
                }
                reload_ajax();
            }
        });
    });

    $("#detail_hasil").on("click", ".btn-akumulasikan", function () {
        let mahasiswa_id = $(this).data("mahasiswa_id");
        let id_hasil_ujian = $(this).data("ujian");//tabel h_ujian

        $.ajax({
            url: base_url + "ujian/akumulasikan",
            data: "ujianid=" + id_hasil_ujian,
            type: "GET",
            success: function (response) {
                if (response.status == true) {
                    Swal({
                        title: "Berhasil",
                        text: "Data Ujian Berhasil Diakumulasi",
                        type: "success"
                    });
                } else {
                    Swal({
                        title: "Gagal",
                        text: "Data Ujian Gagal Diakumulasi",
                        type: "error"
                    });
                }
                reload_ajax();
            }
        });

    });

});

table
    .buttons()
    .container()
    .appendTo("#detail_hasil_wrapper");