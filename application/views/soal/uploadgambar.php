<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title"><?= $subjudul ?></h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="box-body">
        <div class="alert alert-info">
            Unggah gambar/audio/video yang akan dipakai di kolom <b>File</b> / <b>File A&ndash;E</b> saat import soal dari Excel.
            Tulis nama file <b>persis seperti di kolom "Nama di server"</b> pada hasil di bawah.<br>
            <i class="fa fa-lightbulb-o"></i> Lebih praktis: masukkan file Excel dan gambar ke satu <b>.zip</b>, lalu import ZIP tersebut di menu <a href="<?= base_url('soal/import') ?>">Import Soal</a> &mdash; tidak perlu unggah gambar di sini.
        </div>

        <?php if (!empty($hasil)) : ?>
            <table class="table table-bordered table-condensed">
                <thead>
                    <tr>
                        <th>File yang diunggah</th>
                        <th>Nama di server (tulis ini di Excel)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($hasil as $h) : ?>
                        <tr class="<?= $h['error'] ? 'danger' : 'success' ?>">
                            <td><?= html_escape($h['asal']) ?></td>
                            <td>
                                <?php if ($h['error']) : ?>
                                    <i class="fa fa-times"></i> Gagal: <?= html_escape($h['error']) ?>
                                <?php else : ?>
                                    <code><?= html_escape($h['nama']) ?></code>
                                    <?php if ($h['nama'] !== $h['asal']) : ?>
                                        <small class="text-muted">(nama sudah dipakai / disesuaikan sistem)</small>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <form action="<?php echo base_url() . 'soal/upload_image' ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
            <?php for ($i = 1; $i <= 10; $i++) : ?>
                <div class="form-group">
                    <input type="file" name="filefoto<?php echo $i; ?>" accept=".jpg,.jpeg,.png,.gif,.bmp,.mp3,.wav,.mp4,.mpeg,.mpg">
                </div>
            <?php endfor; ?>
            <button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Upload</button>
            <a href="<?= base_url('soal') ?>" class="btn btn-default">Kembali</a>
        </form>
    </div>
</div>
