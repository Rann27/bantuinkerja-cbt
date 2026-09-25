$(document).ready(function () {
    $("#simpanterakhir").hide();
    var t = $('.sisawaktu');
    if (t.length) {
        sisawaktu(t.data('time'), '0');
    }

    buka(1);
    simpan_sementara();

    widget = $(".step");
    btnnext = $(".next");
    btnback = $(".back");
    btnsubmit = $(".submit");

    $(".step, .back, .selesai").hide();
    $("#widget_1").show();
});

function getFormData($form) {
    var unindexed_array = $form.serializeArray();
    var indexed_array = {};
    $.map(unindexed_array, function (n, i) {
        indexed_array[n['name']] = n['value'];
    });
    return indexed_array;
}

function buka(id_widget) {
    $(".next").attr('rel', (id_widget + 1));
    $(".back").attr('rel', (id_widget - 1));
    $(".ragu_ragu").attr('rel', (id_widget));
    cek_status_ragu(id_widget);
    cek_terakhir(id_widget);

    var f_asal = $("#ujian");
    var form = getFormData(f_asal);
    var jml_soal = form.jml_soal;
    jml_soal = parseInt(jml_soal);

    if (id_widget == jml_soal && status_essay=="1") {
        $("#soalke").html("Essay");
    } else {
        $("#soalke").html(id_widget);
    }


    $(".step").hide();
    $("#widget_" + id_widget).show();

    simpan();
}

function next() {
    var berikutnya = $(".next").attr('rel');
    berikutnya = parseInt(berikutnya);
    berikutnya = berikutnya > total_widget ? total_widget : berikutnya;

    var f_asal = $("#ujian");
    var form = getFormData(f_asal);
    var jml_soal = form.jml_soal;
    jml_soal = parseInt(jml_soal);

    if (berikutnya == jml_soal && status_essay=="1") {
        $("#soalke").html("Essay");
    } else {
        $("#soalke").html(berikutnya);
    }



    $(".next").attr('rel', (berikutnya + 1));
    $(".back").attr('rel', (berikutnya - 1));
    $(".ragu_ragu").attr('rel', (berikutnya));
    cek_status_ragu(berikutnya);
    cek_terakhir(berikutnya);

    var sudah_akhir = berikutnya == total_widget ? 1 : 0;

    $(".step").hide();
    $("#widget_" + berikutnya).show();

    if (sudah_akhir == 1) {
        $(".back").show();
        $(".next").hide();
    } else if (sudah_akhir == 0) {
        $(".next").show();
        $(".back").show();
    }

    simpan();
}

function back() {
    var back = $(".back").attr('rel');
    back = parseInt(back);
    back = back < 1 ? 1 : back;

    $("#soalke").html(back);

    $(".back").attr('rel', (back - 1));
    $(".next").attr('rel', (back + 1));
    $(".ragu_ragu").attr('rel', (back));
    cek_status_ragu(back);
    cek_terakhir(back);

    $(".step").hide();
    $("#widget_" + back).show();

    var sudah_awal = back == 1 ? 1 : 0;

    $(".step").hide();
    $("#widget_" + back).show();

    if (sudah_awal == 1) {
        $(".back").hide();
        $(".next").show();
    } else if (sudah_awal == 0) {
        $(".next").show();
        $(".back").show();
    }

    simpan();
}

function tidak_jawab() {
    var id_step = $(".ragu_ragu").attr('rel');
    var status_ragu = $("#rg_" + id_step).val();

    if (status_ragu == "N") {
        $("#rg_" + id_step).val('Y');
        $("#btn_soal_" + id_step).removeClass('btn-success');
        $("#btn_soal_" + id_step).addClass('btn-warning');

    } else {
        $("#rg_" + id_step).val('N');
        $("#btn_soal_" + id_step).removeClass('btn-warning');
        $("#btn_soal_" + id_step).addClass('btn-success');
    }

    cek_status_ragu(id_step);

    simpan();
}

function cek_status_ragu(id_soal) {
    var status_ragu = $("#rg_" + id_soal).val();

    if (status_ragu == "N") {
        $(".ragu_ragu").html('Ragu');
    } else {
        $(".ragu_ragu").html('Tidak Ragu');
    }
}

function cek_terakhir(id_soal) {
    var jml_soal = $("#jml_soal").val();
    jml_soal = (parseInt(jml_soal));



    if (jml_soal === id_soal) {
        $('.next').hide();
        $(".back").show();
        // $(".selesai, .back").show();
    } else {
        $('.next').show();
        // $(".selesai, .back").hide();
        $(" .back").hide();
    }
}

function simpan_sementara() {
    var f_asal = $("#ujian");
    var form = getFormData(f_asal);
    //form = JSON.stringify(form);
    var jml_soal = form.jml_soal;
    jml_soal = parseInt(jml_soal);

    var hasil_jawaban = "";
    var ceklengkap = 0;
    if(status_essay=="0"){
        jml_soal = jml_soal - 1;
    }

    for (var i = 1; i <= jml_soal; i++) {
        var idx = 'opsi_' + i;
        var idx2 = 'rg_' + i;
        var jawab = form[idx];
        var ragu = form[idx2];
        var text = i;
        if(status_essay=="1"){
            if (i == jml_soal) {
                text = "Essay";
            }
        }

        if (jawab != undefined) {
            if (ragu == "Y") {
                if (jawab == "-") {
                    hasil_jawaban += '<a id="btn_soal_' + (i) + '" class="btn btn-default btn_soal btn-sm" onclick="return buka(' + (i) + ');">' + (text) + ". " + jawab + "</a>";
                } else {
                    hasil_jawaban += '<a id="btn_soal_' + (i) + '" class="btn btn-warning btn_soal btn-sm" onclick="return buka(' + (i) + ');">' + (text) + ". " + jawab + "</a>";
                }
            } else {
                if (jawab == "-") {
                    hasil_jawaban += '<a id="btn_soal_' + (i) + '" class="btn btn-default btn_soal btn-sm" onclick="return buka(' + (i) + ');">' + (text) + ". " + jawab + "</a>";
                } else {
                    hasil_jawaban += '<a id="btn_soal_' + (i) + '" class="btn btn-success btn_soal btn-sm" onclick="return buka(' + (i) + ');">' + (text) + ". " + jawab + "</a>";
                }
            }
            ceklengkap = ceklengkap + 1;
        } else {
            hasil_jawaban += '<a id="btn_soal_' + (i) + '" class="btn btn-default btn_soal btn-sm" onclick="return buka(' + (i) + ');">' + (text) + ". -</a>";
        }
    }
    ceklengkap = parseInt(ceklengkap);
    if (ceklengkap == jml_soal - 1) {
        var t = $('.sisawaktu');
        if (t.length) {
            sisawaktu(t.data('time'), 'lengkap');
        }
    }
    $("#tampil_jawaban").html('<div id="yes"></div>' + hasil_jawaban);
}

function simpan() {
    simpan_sementara();
    var form = $("#ujian");

    $.ajax({
        type: "POST",
        url: base_url + "ujian/simpan_satu",
        data: form.serialize(),
        dataType: 'json',
        success: function (data) {
            // $('.ajax-loading').show();
            console.log(data);
        }
    });
}

function selesai() {
    simpan();
    ajaxcsrf();
    $.ajax({
        type: "POST",
        url: base_url + "ujian/simpan_akhir",
        data: { id: id_tes },
        beforeSend: function () {
            simpan();
            // $('.ajax-loading').show();    
        },
        success: function (r) {
            console.log(r);
            if (r.status) {
                window.location.href = base_url + 'ujian/list';
            }
        }
    });
}

function waktuHabis() {
    selesai();
    alert('Waktu ujian telah habis!');
}

function simpan_akhir() {
    simpan();
    if (confirm('Yakin ingin mengakhiri tes?')) {
        selesai();
    }
}