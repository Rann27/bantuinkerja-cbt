<?php if ($this->ion_auth->is_admin()) : ?>
    <div class="row">
        <?php foreach ($info_box as $info) : ?>
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-<?= $info->box ?>">
                    <div class="inner">
                        <h3><?= $info->total; ?></h3>
                        <p><?= $info->title; ?></p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-<?= $info->icon ?>"></i>
                    </div>
                    <a href="<?= base_url() . strtolower($info->title); ?>" class="small-box-footer">
                        More info <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

<?php elseif ($this->ion_auth->in_group('dosen')) : ?>

    <div class="row">
        <div class="col-sm-4">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Informasi Akun</h3>
                </div>
                <table class="table table-hover">
                    <tr>
                        <th>Nama</th>
                        <td><?= $dosen->nama_dosen ?></td>
                    </tr>
                    <tr>
                        <th>NIP</th>
                        <td><?= $dosen->nip ?></td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td><?= $dosen->email ?></td>
                    </tr>
                    <tr>
                        <th>Mata Kuliah</th>
                        <td><?= $dosen->nama_matkul ?> </td>
                    </tr>
                    <tr>
                        <th>Daftar Kelas</th>
                        <td>
                            <ol class="pl-4">
                                <?php foreach ($kelas as $k) : ?>
                                    <li><?= $k->nama_kelas ?> <?= $k->nama_jurusan ?></li>
                                <?php endforeach; ?>
                            </ol>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="col-sm-8">
            <div class="box box-solid">
                <div class="box-header bg-purple">
                    <h3 class="box-title">Pemberitahuan</h3>
                </div>
                <div class="box-body">
                    <p>Ada beberapa hal yang perlu diberitahukan untuk pengisian Soal</p>
                    <ul class="pl-4">
                        <li>Sesuaikan ID Guru dan ID Matpel jika melakukan penambahan dengan metode Import</li>
                        <li>Kode untuk Ujian akan sama di semua Matpel</li>
                        <li>Hasil Ujian bisa Dicetak Per Kelas dan Per Matpel</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

<?php elseif ($this->ion_auth->in_group('spp')) : ?>
    <div class="row">
        <div class="col-sm-4">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Informasi Akun</h3>
                </div>
            </div>
        </div>
    </div>

<?php else : ?>

    <div class="row">
        <div class="col-sm-4">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Informasi Akun</h3>
                </div>
                <table class="table table-hover">
                    <tr>
                        <th>NIM</th>
                        <td><?= $mahasiswa->nim ?></td>
                    </tr>
                    <tr>
                        <th>Nama</th>
                        <td><?= $mahasiswa->nama ?></td>
                    </tr>
                    <tr>
                        <th>Jenis Kelamin</th>
                        <td><?= $mahasiswa->jenis_kelamin === 'L' ? "Laki-laki" : "Perempuan"; ?></td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td><?= $mahasiswa->email ?></td>
                    </tr>
                    <tr>
                        <th>Jurusan</th>
                        <td><?= $mahasiswa->nama_jurusan ?></td>
                    </tr>
                    <tr>
                        <th>Kelas</th>
                        <td><?= $mahasiswa->nama_kelas ?></td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="col-sm-8">
            <div class="box box-solid">
                <div class="box-header bg-purple">
                    <h3 class="box-title">Pemberitahuan</h3>
                </div>
                <div class="box-body">
                    <p style="font-weight: bold;font-size:18px">Beberapa Peraturan Ujian yang Sangat Perlu Diperhatikan dan Dilakukan Oleh Peserta :</p>
                    <ul class="pl-4" style="font-size:16px;line-height:2em">
                        <li>Gunakan Token Kode yang Telah disediakan Oleh Panitia untuk Memulai Ujian</li>
                        <li>Jangan Meninggalkan Halaman (Menutup Browser/Membuka Tab Baru) Secara Sengaja Agar Ujian Tidak Berakhir </li>
                        <li>Pastikan Semua Soal Dijawab Sebelum Submit Akhir/Selesai Ujian</li>
                        <li>Tombol <b>Selesai</b> Akan Muncul Jika Semua Soal Telah Terisi Jawabannya</li>
                        <li>Jawaban Soal Essay ditulis di Kertas Yang Telah Disediakan Pengawas</li>
                        <li>Tidak Boleh Saling Contek dengan Peserta Lain</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

<?php endif; ?>