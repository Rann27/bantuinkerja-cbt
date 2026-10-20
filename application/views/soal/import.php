<?php
$pesan = isset($pesan) ? $pesan : [];
$staging = isset($pesan['staging']) ? $pesan['staging'] : '';
$staging_files = isset($pesan['staging_files']) ? $pesan['staging_files'] : [];

// Pratinjau media: file baru dari ZIP masih di folder staging, file lama ada di bank soal
$media_preview = function ($nama) use ($staging, $staging_files) {
    if ($nama === '' || $nama === null) {
        return '';
    }
    $path = isset($staging_files[$nama]) ? $staging . $nama : 'uploads/bank_soal/' . $nama;
    return '<div style="max-width:120px">' . tampil_media($path, '100%') . '</div>';
};
?>
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
            <li>Import soal dari Excel (<b>.xlsx</b>/.xls/.csv) menggunakan format yang sudah disediakan.</li>
            <li>Soal boleh lebih dari satu baris (tekan <b>Alt+Enter</b> di dalam sel). Format <b>tebal</b>, <i>miring</i>, <u>garis bawah</u>, subscript (H<sub>2</sub>O) dan superscript (x<sup>2</sup>) dari Excel ikut terbawa.</li>
            <li><b>Soal bergambar/audio/video:</b> tulis nama file di kolom <b>File</b> (gambar soal) atau <b>File A&ndash;E</b> (gambar opsi), lalu masukkan file Excel dan semua file tersebut ke dalam satu <b>.zip</b> dan import ZIP-nya. Nama file akan diganti otomatis oleh sistem agar tidak bentrok. Format: jpg, png, gif, bmp, mp3, wav, mp4.</li>
            <li>Kolom <b>Tipe File</b> boleh dikosongkan (terisi otomatis). Jawaban diisi A, B, C, D, atau E.</li>
            <li>Untuk data guru, hanya bisa diisi menggunakan ID Guru dan ID Matpel yang sesuai. <a data-toggle="modal" href="#jurusanId" style="text-decoration:none" class="btn btn-xs btn-primary">Lihat ID</a>.</li>
        </ul>
        <div class="text-center">
            <a href="<?= base_url('uploads/import/format/soal.xlsx') ?>" class="btn-default btn"><i class="fa fa-file-excel-o"></i> Download Format</a>
            <a href="<?= base_url('uploads/import/format/contoh_import_soal.zip') ?>" class="btn-default btn"><i class="fa fa-file-archive-o"></i> Contoh ZIP (soal bergambar)</a>
        </div>
        <br>
        <?php if (!empty($pesan['error'])) : ?>
            <div class="alert alert-danger"><?= implode('<br>', array_map('html_escape', $pesan['error'])) ?></div>
        <?php endif; ?>
        <?php if (!empty($pesan['peringatan'])) : ?>
            <div class="alert alert-warning"><b>Perhatian:</b><br><?= implode('<br>', array_map('html_escape', $pesan['peringatan'])) ?></div>
        <?php endif; ?>
        <div class="row">
            <?= form_open_multipart('soal/preview'); ?>
            <label for="file" class="col-sm-offset-1 col-sm-3 text-right">Pilih File</label>
            <div class="col-sm-4">
                <div class="form-group">
                    <input type="file" name="upload_file" accept=".xlsx,.xls,.csv,.zip">
                    <p class="help-block">.xlsx / .xls / .csv, atau .zip (Excel + gambar). Maks 50 MB.</p>
                </div>
            </div>
            <div class="col-sm-3">
                <button name="preview" type="submit" class="btn btn-sm btn-success">Preview</button>
            </div>
            <?= form_close(); ?>
            <div class="col-sm-12">
                <?php if (isset($import)) : ?>
                    <br>
                    <h4>Preview Data</h4>
                    <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <td>No</td>
                                <td>Guru</td>
                                <td>Mata Pelajaran</td>
                                <td>Bobot</td>
                                <td>Soal</td>
                                <td>Opsi A</td>
                                <td>Opsi B</td>
                                <td>Opsi C</td>
                                <td>Opsi D</td>
                                <td>Opsi E</td>
                                <td>Jawaban</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $status = true;
                            $jml_error = 0;
                            if (empty($import)) {
                                $status = false;
                                echo '<tr><td colspan="11" class="text-center">Data kosong! pastikan anda menggunakan format yang telah disediakan.</td></tr>';
                            } else {
                                $no = 1;
                                foreach ($import as $data) :
                            ?>
                                    <tr class="<?= empty($data['error']) ? '' : 'danger'; ?>">
                                        <td><?= $no++; ?><br><small class="text-muted">baris <?= $data['_baris'] ?></small></td>
                                        <td class="<?= $data['dosen_id'] == null ? 'bg-danger' : ''; ?>">
                                            <?= $data['dosen_id'] == null ? 'BELUM DIISI' : html_escape($data['dosen_id']); ?>
                                        </td>
                                        <td class="<?= $data['matkul_id'] == null ? 'bg-danger' : ''; ?>">
                                            <?= $data['matkul_id'] == null ? 'BELUM DIISI' : html_escape($data['matkul_id']); ?>
                                        </td>
                                        <td class="<?= $data['bobot'] == null ? 'bg-danger' : ''; ?>">
                                            <?= $data['bobot'] == null ? 'BELUM DIISI' : html_escape($data['bobot']); ?>
                                        </td>
                                        <td class="<?= $data['soal'] == null && $data['file'] == '' ? 'bg-danger' : ''; ?>">
                                            <?= $media_preview($data['file']); ?>
                                            <?= $data['soal']; ?>
                                            <?php foreach ($data['error'] as $e) : ?>
                                                <div class="text-danger"><i class="fa fa-exclamation-circle"></i> <?= html_escape($e) ?></div>
                                            <?php endforeach; ?>
                                        </td>
                                        <?php foreach (['a', 'b', 'c', 'd', 'e'] as $o) : ?>
                                            <td class="<?= $data['opsi_' . $o] == null && $data['file_' . $o] == '' ? 'bg-danger' : ''; ?>">
                                                <?= $media_preview($data['file_' . $o]); ?>
                                                <?= $data['opsi_' . $o] == null && $data['file_' . $o] == '' ? 'BELUM DIISI' : $data['opsi_' . $o]; ?>
                                            </td>
                                        <?php endforeach; ?>
                                        <td class="<?= in_array($data['jawaban'], ['A', 'B', 'C', 'D', 'E'], true) ? '' : 'bg-danger'; ?>">
                                            <?= $data['jawaban'] == null ? 'BELUM DIISI' : html_escape($data['jawaban']); ?>
                                        </td>
                                    </tr>
                            <?php
                                    if ($data['dosen_id'] == null || $data['matkul_id'] == null || !empty($data['error'])) {
                                        $status = false;
                                        $jml_error++;
                                    }
                                endforeach;
                            }
                            ?>
                        </tbody>
                    </table>
                    </div>
                    <?php if ($status) : ?>
                        <?php
                        // Kolom bantu preview tidak perlu ikut dikirim
                        $kirim = array_map(function ($d) {
                            unset($d['error'], $d['_baris']);
                            return $d;
                        }, $import);
                        ?>
                        <?= form_open('soal/do_import', null, ['data' => json_encode($kirim)]); ?>
                        <button type='submit' class='btn btn-block btn-flat bg-purple'>Import <?= count($import) ?> Soal</button>
                        <?= form_close(); ?>
                    <?php elseif (!empty($import)) : ?>
                        <div class="alert alert-danger"><?= $jml_error ?> baris masih bermasalah (ditandai merah). Perbaiki file lalu preview ulang.</div>
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