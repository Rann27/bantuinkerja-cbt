<div class="login-box pt-1">
	<!-- /.login-logo -->
	<div class="login-box-body">
		<h3 class="text-center mt-0 mb-4">
			<b>C</b>omputer <b>B</b>ased <b>P</b>sikotes
		</h3> 
		<p class="login-box-msg">Masukkan Data Anda untuk Daftar</p>

		<div id="infoMessage" class="text-center"><?php echo $message;?></div>

		<form action="<?php echo base_url('auth/register');?>" method="POST">
		<div class="form-group">
			<label for="nim">NIM</label>
			<input autofocus="autofocus" onfocus="this.select()" placeholder="NIM" type="text" name="nim" class="form-control">
			<input type="hidden" name="<?php echo $this->security->get_csrf_token_name();?>" value="<?php echo $this->security->get_csrf_hash();?>">
			<small class="help-block"></small>
		</div>
		<div class="form-group">
			<label for="nama">Nama</label>
			<input placeholder="Nama" type="text" name="nama" class="form-control">
			<small class="help-block"></small>
		</div>
		<div class="form-group">
			<label for="email">Email</label>
			<input placeholder="Email" type="email" name="email" class="form-control">
			<small class="help-block"></small>
		</div>
		<div class="form-group">
			<label for="jenis_kelamin">Jenis Kelamin</label>
			<select name="jenis_kelamin" class="form-control select2">
				<option value="">-- Pilih --</option>
				<option value="L">Laki-laki</option>
				<option value="P">Perempuan</option>
			</select>
			<small class="help-block"></small>
		</div>
		<div class="row">
			<div class="col-xs-8">
				<div class="checkbox icheck">
					
				</div>
			</div>
			<!-- /.col -->
			<div class="col-xs-4">
				<button type="submit" class="btn btn-primary btn-block btn-flat">Daftar</button>
			</div>
			<!-- /.col -->
		</div>
		</form>

		<a href="<?=base_url()?>" class="text-center">Sudah Punya Akun?</a>
	</div>
</div>

<script type="text/javascript">
	let base_url = '<?=base_url();?>';
</script>
<script src="<?=base_url()?>assets/dist/js/app/auth/login.js"></script>