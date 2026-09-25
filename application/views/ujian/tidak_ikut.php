<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title"><?= $subjudul ?></h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12 mb-4">
                <a href="<?= base_url() ?>hasilujian" class="btn btn-flat btn-sm btn-default"><i class="fa fa-arrow-left"></i> Kembali</a>
                <!-- <button type="button" onclick="reload_ajax()" class="btn btn-flat btn-sm bg-purple"><i class="fa fa-refresh"></i> Reload</button> -->
                <div class="pull-right">

                </div>
            </div>
            <div class="col-sm-6">
                <table class="table w-100">
                    <tr>
                        <th>Nama Ujian</th>
                        <td><?= $ujian->nama_ujian ?></td>
                    </tr>
                    <tr>
                        <th>Jumlah Soal</th>
                        <td><?= $ujian->jumlah_soal ?></td>
                    </tr>
                    <tr>
                        <th>Waktu</th>
                        <td><?= $ujian->waktu ?> Menit</td>
                    </tr>
                </table>
            </div>
            <div class="col-sm-6">
                <table class="table w-100">
                    <tr>
                        <th>Tanggal Mulai</th>
                        <td><?= strftime('%A, %d %B %Y', strtotime($ujian->tgl_mulai)) ?></td>
                    </tr>
                    <tr>
                        <th>Tanggal Selasi</th>
                        <td><?= strftime('%A, %d %B %Y', strtotime($ujian->terlambat)) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="table-responsive px-4 pb-3" style="border: 0">
        <table id="detail_hasil" class="w-100 table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>NIS</th>
                    <th>Nama</th>
                    <th>Kelas</th>
                    <th>Jurusan</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1;
                foreach ($data as $d) { ?>
                    <tr>
                        <td><?= $no ?></td>
                        <td><?= $d->nim ?></td>
                        <td><?= $d->nama ?></td>
                        <td><?= $d->nama_kelas ?></td>
                        <td><?= $d->nama_jurusan ?></td>
                    </tr>
                <?php $no++;
                } ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>No.</th>
                    <th>NIS</th>
                    <th>Nama</th>
                    <th>Kelas</th>
                    <th>Jurusan</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<script type="text/javascript">
    var id = '<?= $this->uri->segment(3) ?>';
    $(document).ready(function() {
        $("#detail_hasil").DataTable({});
    });
</script>