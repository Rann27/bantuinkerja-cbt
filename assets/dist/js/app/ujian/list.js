var table;

$(document).ready(function () {

    ajaxcsrf();
    // var isToday = (today.toDateString());
    table = $("#ujian").DataTable({
        initComplete: function () {
            var api = this.api();
            $('#ujian_filter input')
                .off('.DT')
                .on('keyup.DT', function (e) {
                    api.search(this.value).draw();
                });
        },
        oLanguage: {
            sProcessing: "loading..."
        },
        processing: true,
        serverSide: true,
        ajax: {
            "url": base_url + "ujian/list_json",
            "type": "POST",
        },
        columns: [
            {
                "data": "id_ujian",
                "orderable": false,
                "searchable": false
            },
            { "data": 'nama_ujian' },
            { "data": 'nama_matkul' },
            { "data": 'nama_dosen' },
            { "data": 'jumlah_soal' },
            { "data": 'waktu' },
            {
                "searchable": false,
                "orderable": false
            }
        ],
        columnDefs: [
            {
                "targets": 6,
                "data": {
                    "id_ujian": "id_ujian",
                    "statusujian": "statusujian",
                    "ada": "ada",
                    "cektanggal": "cektanggal",
					"status_curang": "status_curang"
                },
                "render": function (data, type, row, meta) {
                    var btn;
                    if (data.ada > 0) {
                        const d = new Date(data.cektanggal);
                        const today = new Date();
                        var bool = (d > today);//tanggal selesai kurang dari t
                        if (data.statusujian == "Y" && bool==true && data.status_curang=="0") {
                            btn = `<a class="btn btn-xs btn-primary" href="${base_url}ujian/lanjutkanujian?key=${data.id_ujian}">
                            <i class="fa fa-pencil"></i> Lanjut Ujian</a>`;
                        } else {
                            btn=`-`;
                        }

                    } else {
                        btn = `<a class="btn btn-xs btn-primary" href="${base_url}ujian/token/${data.id_ujian}">
								<i class="fa fa-pencil"></i> Ikut Ujian
							</a>`;
                    }
                    return `<div class="text-center">
									${btn}
								</div>`;
                }
            },
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
});