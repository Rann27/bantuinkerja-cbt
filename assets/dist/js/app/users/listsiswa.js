var table;

$(document).ready(function () {
    ajaxcsrf();

    //Load Data Siswa
    loaddatasiswa();


    $('#kelas').on('change', function () {
        loadjurusan($(this).val());
        console.log($(this).val());
        $("#users").dataTable().fnDestroy();
        var jurusan = $("#jurusan").val();
        if (jurusan == '') { jurusan = undefined; }
        var kelas = $("#kelas").val();
        if (kelas == '') { kelas = undefined; }
        var status = $("#status").val();
        if (status == '') { status = undefined; }
        loaddatasiswa(kelas, status, jurusan);
    });

    // Load Jurusan by Kelas
    $('#jurusan').on('change', function () {
        $("#users").dataTable().fnDestroy();
        var jurusan = $("#jurusan").val();
        if (jurusan == '') { jurusan = undefined; }
        var kelas = $("#kelas").val();
        if (kelas == '') { kelas = undefined; }
        var status = $("#status").val();
        if (status == '') { status = undefined; }
        loaddatasiswa(kelas, status, jurusan);
    });

    $('#status').on('change', function () {
        $("#users").dataTable().fnDestroy();
        var jurusan = $("#jurusan").val();
        if (jurusan == '') { jurusan = undefined; }
        var kelas = $("#kelas").val();
        if (kelas == '') { kelas = undefined; }
        var status = $("#status").val();
        if (status == '') { status = undefined; }
        loaddatasiswa(kelas, status, jurusan);

    });



    $("#show_me").on("change", function () {
        let src = base_url + "users/data";
        let url = $(this).prop("checked") === true ? src : src + "/" + user_id;
        table.ajax.url(url).load();
    });

    $("#users").on("click", ".btn-aktif", function () {
        let id = $(this).data("id");
        var status = $(this).data("status");
        $.ajax({
            url: base_url + "users/ubahstatus",
            data: "id=" + id + "&status=" + status,
            type: "GET",
            success: function (response) {
                if (response.status == true) {
                    Swal({
                        title: "Berhasil",
                        text: "Berhasil Update Status",
                        type: "success"
                    });
                } else {
                    Swal({
                        title: "Gagal",
                        text: "Gagal Update Status",
                        type: "error"
                    });
                }
                reload_ajax();
            }
        });
    });

});

function loaddatasiswa(idkelas, statussiswa, jurusan) {
    table = $("#users").DataTable({
        initComplete: function () {
            var api = this.api();
            $("#users_filter input")
                .off(".DT")
                .on("keyup.DT", function (e) {
                    api.search(this.value).draw();
                });
        },
        // "pageLength": 20,
        dom:
            "<'row'<'col-sm-3'l><'col-sm-6 text-center'B><'col-sm-3'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [
            {
                extend: "copy",
                exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7] }
            },
            {
                extend: "print",
                exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7] }
            },
            {
                extend: "excel",
                exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7] }
            },
            {
                extend: "pdf",
                exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7] }
            }
        ],
        oLanguage: {
            sProcessing: "loading..."
        },
        processing: true,
        serverSide: true,
        ajax: {
            url: base_url + "users/datasiswa/" + user_id,
            type: "POST",
            data: {
                idkelas: idkelas,
                status: statussiswa,
                jurusan: jurusan
            },
        },
        columns: [
            {
                data: "id",
                orderable: false,
                searchable: false
            },
            { data: "nim" },
            { data: "nama" },
            { data: "email" },
            { data: "nama_kelas" },
            { data: "nama_jurusan" },
        ],
        columnDefs: [
            {
                targets: 6,
                orderable: false,
                searchable: false,
                title: "Status",
                data: "active",
                render: function (data, type, row, meta) {
                    if (data === "1") {
                        return `<div class="text-center">
                                <span class="badge bg-green">Active</span>
                            </div>`;
                    } else {
                        return `<div class="text-center">
                                <span class="badge bg-red">Not Active</span>
                            </div>`;
                    }
                }
            },
            {
                targets: 7,
                searchable: false,
                data: { id: "id", aktif: "active" },
                render: function (data, type, row, meta) {
                    if (data === user_id) {
                        return `<div class="text-center">
                                <a class="btn btn-xs bg-primary" href="${base_url}users/edit/${data}">
                                    <i class="fa fa-cog fa-spin"></i>
                                </a>
                            </div>`;
                    } else {
                        if (data.active === "1") {
                            return `<div class="text-center">
                            <button data-id="${data.id}" data-status="${data.active}" type="button" class="btn btn-xs btn-danger btn-aktif">
                            Non Aktifkan
							</button>
                            </div>`
                        } else {
                            return `<div class="text-center">
                            <button data-id="${data.id}" data-status="${data.active}" type="button" class="btn btn-xs btn-success btn-aktif">
                            Aktifkan
							</button>
                        </div>`;
                        }
                    }
                }
            }
        ],
        order: [[1, "asc"]],
        rowId: function (a) {
            return a;
        },
        rowCallback: function (row, data, iDisplayIndex) {
            var info = this.fnPagingInfo();
            var page = info.iPage;
            var length = info.iLength;
            var index = page * length + (iDisplayIndex + 1);
            $("td:eq(0)", row).html(index);
        }
    });

    table
        .buttons()
        .container()
        .appendTo("#users_wrapper .col-md-6:eq(0)");
}

function loadjurusan(kelas) {
    $('#jurusan').find('option').not(':first').remove();
    $.getJSON(base_url + 'jurusan/jurusan_by_kelas/' + kelas, function (data) {
        // console.log(data);
        var option = [];
        for (let i = 0; i < data.length; i++) {
            option.push({
                id: data[i].id_jurusan,
                text: data[i].nama_jurusan
            });
        }
        $('#jurusan').select2({
            data: option
        });
    });
}

function hapus(id) {
    Swal({
        title: "Anda yakin?",
        text: "Data akan dihapus.",
        type: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Hapus!"
    }).then(result => {
        if (result.value) {
            $.getJSON(base_url + "users/delete/" + id, function (data) {
                Swal({
                    title: data.status ? "Berhasil" : "Gagal",
                    text: data.status
                        ? "User berhasil dihapus"
                        : "User gagal dihapus",
                    type: data.status ? "success" : "error"
                });
                reload_ajax();
            });
        }
    });
}
