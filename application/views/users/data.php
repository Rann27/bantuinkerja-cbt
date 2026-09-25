<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title">Master <?= $subjudul ?></h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="box-body">
        <div class="col-md-12">
            <form class="row" action="<?php echo base_url(). 'kartu/kartupeserta'; ?>" method="post">
                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="kelas">Kelas</label>
                        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name();?>" value="<?php echo $this->security->get_csrf_hash();?>">
                        <select name="id_kelas" id="kelas" class="form-control select2" style="width: 100%!important">
                            <option value="" disabled selected>Pilih Kelas</option>
                            <?php foreach ($datakelas as $row) : ?>
                                <option value="<?= $row->id_kelas ?>"><?= $row->nama_kelas . ' ' . $row->nama_jurusan ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="help-block"></small>
                    </div>
                </div>
                <div class="col-sm-4" style="margin-top:25px">
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Cetak Kartu Peserta</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="mt-2 mb-3">
            <button type="button" onclick="reload_ajax()" class="btn btn-sm btn-flat bg-purple"><i class="fa fa-refresh"></i> Reload</button>
            <div class="pull-right">
                <label for="show_me">
                    <input type="checkbox" id="show_me">
                    Tampilkan saya
                </label>
            </div>
        </div>
    </div>
    <div class="table-responsive px-4 pb-3" style="border: 0">
        <table id="users" class="w-100 table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Level</th>
                    <th>Created On</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th>No.</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Level</th>
                    <th>Created On</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<script type="text/javascript">
    var user_id = '<?= $user->id ?>';
</script>

<script src="<?= base_url() ?>assets/dist/js/app/users/data.js"></script>