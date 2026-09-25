<div class="box">
	<div class="box-header with-border">
		<h3 class="box-title">List Soal <?= $matkul->nama_matkul; ?></h3>
		<div class="box-tools pull-right">
			<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
			</button>
		</div>
	</div>
	<div class="box-body">
		<div class="row">
			<div class="col-sm-4">
				<button type="button" onclick="bulk_delete()" class="btn btn-flat btn-sm bg-red"><i class="fa fa-trash"></i> Bulk Delete</button>
			</div>
			<div class="form-group col-sm-4 text-center">
				<?php if ($this->ion_auth->is_admin()) : ?>
                    <input id="matkul_id" value="<?= $matkul->nama_matkul; ?>" type="text" readonly="readonly" class="form-control">
				<?php endif; ?>
				<?php if ($this->ion_auth->in_group('dosen')) : ?>
					<input id="matkul_id" value="<?= $matkul->nama_matkul; ?>" type="text" readonly="readonly" class="form-control">
				<?php endif; ?>
			</div>
			<div class="col-sm-4">
				<div class="pull-right">
					<a href="<?= base_url('soal') ?>" class="btn bg-purple btn-flat btn-sm"><i class="fa fa-back"></i> Kembali Ke Menu Soal</a>
				</div>
			</div>
		</div>
	</div>
	<?= form_open('soal/delete', array('id' => 'bulk')) ?>
	<div class="table-responsive px-4 pb-3" style="border: 0">
		<table id="soal" class="w-100 table table-striped table-bordered table-hover">
			<thead>
				<tr>
					<th class="text-center">
						<input type="checkbox" class="select_all">
					</th>
					<th width="25">No.</th>
					<!-- <th>Nama Ujian</th>
					<th>Mapel</th> -->
					<th>Soal</th>
					<th>Tanggal</th>
					<th class="text-center">Aksi</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th class="text-center">
						<input type="checkbox" class="select_all">
					</th>
					<th width="25">No.</th>
					<!-- <th>Nama Ujian</th>
					<th>Mapel</th> -->
					<th>Soal</th>
					<th>Tanggal</th>
					<th class="text-center">Aksi</th>
				</tr>
			</tfoot>
		</table>
	</div>
	<?= form_close(); ?>
</div>

<script src="<?= base_url() ?>assets/dist/js/app/soal/detailsoal.js"></script>

<?php if ($this->ion_auth->is_admin()) : ?>
	<script type="text/javascript">
		$(document).ready(function() {
            let id_matkul = '<?= $matkul->matkul_id ?>';
			let id_dosen = '<?= $matkul->id_dosen ?>';
			let src = '<?= base_url() ?>soal/detailsoal';
			let url = src + '/' + id_matkul + '/' + id_dosen;

			table.ajax.url(url).load();
		});
	</script>
<?php endif; ?>
<?php if ($this->ion_auth->in_group('dosen')) : ?>
	<script type="text/javascript">
		$(document).ready(function() {
			let id_matkul = '<?= $matkul->matkul_id ?>';
			let id_dosen = '<?= $matkul->id_dosen ?>';
			let src = '<?= base_url() ?>soal/detailsoal';
			let url = src + '/' + id_matkul + '/' + id_dosen;

			table.ajax.url(url).load();
		});
	</script>
<?php endif; ?>