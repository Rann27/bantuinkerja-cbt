<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title">Master <?= $subjudul ?></h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="box-body">
        <div class="mt-2 mb-3">
            <button type="button" onclick="reload_ajax()" class="btn btn-sm btn-flat bg-purple"><i class="fa fa-refresh"></i> Reload</button>
        </div>
        <div class="row">
            <div class="form-group  col-md-4">
                <label for="kelas">Kelas</label>
                <select id="kelas" name="kelas" class="form-control select2">
                    <option value="">-- Pilih --</option>
                    <option value="1">X</option>
                    <option value="2">XI</option>
                    <option value="3">XII</option>
                </select>
                <small class="help-block"></small>
            </div>
            <div class="form-group col-md-4">
                <label for="jurusan">Jurusan</label>
                <select id="jurusan" name="jurusan" class="form-control select2">
                    <option value="">-- Pilih --</option>
                </select>
                <small class="help-block"></small>
            </div>
            <div class="form-group  col-md-4">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control select2">
                    <option value="">-- Pilih --</option>
                    <option value="1" selected>Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
                <small class="help-block"></small>
            </div>
        </div>
    </div>
    <div class="table-responsive px-4 pb-3" style="border: 0">
        <table id="users" class="w-100 table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>NIS</th>
                    <th>Nama Lengkap</th>
                    <th>Email</th>
                    <th>Kelas</th>
                    <th>Jurusan</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th>No.</th>
                    <th>NIS</th>
                    <th>Nama Lengkap</th>
                    <th>Email</th>
                    <th>Kelas</th>
                    <th>Jurusan</th>
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

<script src="<?= base_url() ?>assets/dist/js/app/users/listsiswa.js"></script>