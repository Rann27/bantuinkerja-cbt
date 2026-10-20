</section>
<!-- /.content -->
</div>
<!-- /.container -->
</div>
<!-- /.content-wrapper -->
<footer class="main-footer">
	<div class="container">
		<?= strftime('%A, %d %B %Y') ?>, <span class="live-clock"><?= date('H:i:s') ?></span>
		<div class="pull-right hidden-xs">
			<b>Ujian Online</b> v2
		</div>
	</div>
	<!-- /.container -->
</footer>
</div>
<!-- ./wrapper -->

<script src="<?= base_url() ?>assets/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="<?= base_url() ?>assets/dist/js/adminlte.min.js"></script>
<script src="<?= base_url() ?>assets/bower_components/pace/pace.min.js"></script>

<script type="text/javascript">
	var timerUjian = null;
	var ujianLengkap = false;

	function sisawaktu(t,ceklengkap) {
		if (ceklengkap == 'lengkap') {
			ujianLengkap = true;
		}
		// Cukup satu timer per halaman; panggilan berikutnya hanya memperbarui status lengkap
		if (timerUjian) {
			return;
		}
		// Sisa detik dari server dipakai agar tidak bergantung pada jam/zona waktu perangkat peserta
		var sisa = parseInt($('.sisawaktu').data('sisa'), 10);
		var deadline = isNaN(sisa) ? new Date(t).getTime() : new Date().getTime() + sisa * 1000;

		timerUjian = setInterval(function() {
			var dis = deadline - new Date().getTime();
			if (dis <= 0) {
				clearInterval(timerUjian);
				$('.sisawaktu').html('00:00:00');
				waktuHabis();
				return;
			}
			var h = Math.floor(dis / (1000 * 60 * 60));
			var m = Math.floor((dis % (1000 * 60 * 60)) / (1000 * 60));
			var s = Math.floor((dis % (1000 * 60)) / (1000));
			h = ("0" + h).slice(-2);
			m = ("0" + m).slice(-2);
			s = ("0" + s).slice(-2);
			var cd = h + ":" + m + ":" + s;
			$('.sisawaktu').html(cd);
			if (m < 59 && ujianLengkap) {
				$("#simpanterakhir,.selesai").show();
			}
		}, 250);
	}

	function countdown(t) {
		// Sisa detik dari server (bila ada) agar tidak bergantung jam perangkat peserta
		var sisa = parseInt($('.countdown').data('sisa'), 10);
		var target = isNaN(sisa) ? new Date(t).getTime() : new Date().getTime() + sisa * 1000;
		var x = setInterval(function() {
			var dis = target - new Date().getTime();
			// Muat ulang sekali saat waktu habis. Dulu setTimeout(reload, dis) dipasang tiap detik;
			// dis > ~24,8 hari melebihi batas setTimeout sehingga halaman reload terus-menerus.
			if (dis <= 0) {
				clearInterval(x);
				location.reload();
				return;
			}
			var d = Math.floor(dis / (1000 * 60 * 60 * 24));
			var h = Math.floor((dis % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
			var m = Math.floor((dis % (1000 * 60 * 60)) / (1000 * 60));
			var s = Math.floor((dis % (1000 * 60)) / (1000));
			d = ("0" + d).slice(-2);
			h = ("0" + h).slice(-2);
			m = ("0" + m).slice(-2);
			s = ("0" + s).slice(-2);
			var cd = d + " Hari, " + h + " Jam, " + m + " Menit, " + s + " Detik ";
			$('.countdown').html(cd);
		}, 1000);
	}

	function ajaxcsrf() {
		var csrfname = '<?= $this->security->get_csrf_token_name() ?>';
		var csrfhash = '<?= $this->security->get_csrf_hash() ?>';
		var csrf = {};
		csrf[csrfname] = csrfhash;
		$.ajaxSetup({
			"data": csrf
		});
	}

	$(document).ready(function() {
		setInterval(function() {
			var date = new Date();
			var h = date.getHours(),
				m = date.getMinutes(),
				s = date.getSeconds();
			h = ("0" + h).slice(-2);
			m = ("0" + m).slice(-2);
			s = ("0" + s).slice(-2);

			var time = h + ":" + m + ":" + s;
			$('.live-clock').html(time);
		}, 1000);
	});
</script>
</body>

</html>