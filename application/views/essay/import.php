<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title"><?= $subjudul ?></h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="box-body">
        <ul class="alert alert-info" style="padding-left: 40px">
            <li>Silahkan import data dari excel, menggunakan format yang sudah disediakan</li>
            <li>Data tidak boleh ada yang kosong, harus terisi semua.</li>
            <li>Untuk data matpel dan guru, hanya bisa diisi menggunakan ID Matpel dan Guru yang sesuai. <a data-toggle="modal" href="#jurusanId" style="text-decoration:none" class="btn btn-xs btn-primary">Lihat ID</a>.</li>
        </ul>
        <div class="text-center">
            <a href="<?= base_url('uploads/import/format/formatessay.xls') ?>" class="btn-default btn">Download Format</a>
        </div>
        <br>
        <div class="row">
            <?= form_open_multipart('soalessay/preview'); ?>
            <label for="file" class="col-sm-offset-1 col-sm-3 text-right">Pilih File</label>
            <div class="col-sm-4">
                <div class="form-group">
                    <input type="file" name="upload_file">
                </div>
            </div>
            <div class="col-sm-3">
                <button name="preview" type="submit" class="btn btn-sm btn-success">Preview</button>
            </div>
            <?= form_close(); ?>
            <div class="col-sm-12">
                <?php if (isset($_POST['preview'])) : ?>
                    <br>
                    <h4>Preview Data</h4>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <td>No</td>
                                <td>Guru</td>
                                <td>Mata Pelajaran</td>
                                <td>Soal</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $status = true;
                            if (empty($import)) {
                                echo '<tr><td colspan="2" class="text-center">Data kosong! pastikan anda menggunakan format yang telah disediakan.</td></tr>';
                            } else {
                                $no = 1;
                                foreach ($import as $data) :
                            ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td class="<?= $data['dosen_id'] == null ? 'bg-danger' : ''; ?>">
                                            <?= $data['dosen_id'] == null ? 'BELUM DIISI' : $data['dosen_id']; ?>
                                        </td>
                                        <td class="<?= $data['matkul_id'] == null ? 'bg-danger' : ''; ?>">
                                            <?= $data['matkul_id'] == null ? 'BELUM DIISI' : $data['matkul_id'];; ?>
                                        </td>
                                        <td class="<?= $data['essay_soal'] == null ? 'bg-danger' : ''; ?>">
                                            <?= $data['essay_soal'] == null ? 'BELUM DIISI' : $data['essay_soal'];; ?>
                                        </td>

                                    </tr>
                            <?php
                                    if ($data['dosen_id'] == null || $data['matkul_id'] == null) {
                                        $status = false;
                                    }
                                endforeach;
                            }
                            ?>
                        </tbody>
                    </table>
                    <?php if ($status) : ?>

                        <?= form_open('soalessay/do_import', null, ['data' => json_encode($import)]); ?>
                        <button type='submit' class='btn btn-block btn-flat bg-purple'>Import</button>
                        <?= form_close(); ?>

                    <?php endif; ?>
                    <br>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="jurusanId">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">Data Guru</h4>
            </div>
            <div class="modal-body">
                <table id="jurusan" class="table table-condensed table-striped">
                    <thead>
                        <th>Nama Guru</th>
                        <th>ID Guru</th>
                        <th>ID Matpel</th>
                    </thead>
                    <tbody>
                        <?php foreach ($dosen as $j) : ?>
                            <tr>
                                <td><?= $j->nama_dosen; ?></td>
                                <td><?= $j->id_dosen; ?></td>
                                <td><?= $j->matkul_id; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        let table;
        table = $("#jurusan").DataTable({
            "lengthMenu": [
                [5, 10, 25, 50, 100, -1],
                [5, 10, 25, 50, 100, "All"]
            ],
        });
    });
</script>