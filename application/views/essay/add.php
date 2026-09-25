<div class="row">
    <div class="col-sm-12">
        <?= form_open_multipart('soalessay/save', array('id' => 'formsoal'), array('method' => 'add')); ?>
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
                    <div class="col-sm-8 col-sm-offset-2">
                        <div class="form-group col-sm-12">
                            <label>Guru (Mata Mata Pelajaran)</label>
                            <?php if ($this->ion_auth->is_admin()) : ?>
                                <select name="dosen_id" required="required" id="dosen_id" class="select2 form-group" style="width:100% !important">
                                    <option value="" disabled selected>Pilih Guru</option>
                                    <?php foreach ($dosen as $d) : ?>
                                        <option value="<?= $d->id_dosen ?>:<?= $d->matkul_id ?>"><?= $d->nama_dosen ?> (<?= $d->nama_matkul ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="help-block" style="color: #dc3545"><?= form_error('dosen_id') ?></small>
                            <?php else : ?>
                                <input type="hidden" name="dosen_id" value="<?= $dosen->id_dosen; ?>">
                                <input type="hidden" name="matkul_id" value="<?= $dosen->matkul_id; ?>">
                                <input type="text" readonly="readonly" class="form-control" value="<?= $dosen->nama_dosen; ?> (<?= $dosen->nama_matkul; ?>)">
                            <?php endif; ?>
                        </div>
                        <div class="col-sm-12">
                            <label for="soal" class="control-label">Soal</label>
                            <div class="form-group">
                                <input type="file" name="file_soal" class="form-control">
                                <small class="help-block" style="color: #dc3545"><?= form_error('file_soal') ?></small>
                            </div>
                            <div class="form-group">
                                <textarea name="soal" id="soal" class="form-control ckeditor"><?= set_value('soal') ?></textarea>
                                <small class="help-block" style="color: #dc3545"><?= form_error('soal') ?></small>
                            </div>
                        </div>

                        <div class="form-group pull-right">
                            <a href="<?= base_url('soalessay') ?>" class="btn btn-flat btn-default"><i class="fa fa-arrow-left"></i> Batal</a>
                            <button type="submit" id="submit" class="btn btn-flat bg-purple"><i class="fa fa-save"></i> Simpan</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?= form_close(); ?>
    </div>
</div>