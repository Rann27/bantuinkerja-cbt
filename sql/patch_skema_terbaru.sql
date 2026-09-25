-- Patch skema: melengkapi ci_online_test.sql agar sesuai dengan kode saat ini.
-- Jalankan SETELAH import ci_online_test.sql.
-- Struktur direkonstruksi dari kode (controllers/models), bukan dari dump server.

-- m_ujian: fitur essay, anti curang, dan status aktif ujian
ALTER TABLE `m_ujian`
  ADD COLUMN `status_essay` varchar(1) NOT NULL DEFAULT '0',
  ADD COLUMN `status_curang` varchar(1) NOT NULL DEFAULT '0',
  ADD COLUMN `status_ujian` varchar(1) NOT NULL DEFAULT '1';

-- h_ujian: nilai essay per peserta
ALTER TABLE `h_ujian`
  ADD COLUMN `nilai_essay` decimal(10,2) DEFAULT NULL;

-- kelas: kode kelas (dipakai filter menu SPP)
ALTER TABLE `kelas`
  ADD COLUMN `code_kelas` varchar(30) DEFAULT NULL;
UPDATE `kelas` SET `code_kelas` = `nama_kelas` WHERE `code_kelas` IS NULL;

-- Bank soal essay
CREATE TABLE IF NOT EXISTS `soal_essay` (
  `id_soal_essay` int(11) NOT NULL AUTO_INCREMENT,
  `dosen_id` int(11) NOT NULL,
  `matkul_id` int(11) NOT NULL,
  `essay_soal` longtext NOT NULL,
  `file_essay` varchar(255) NOT NULL DEFAULT '',
  `tipe_file_essay` varchar(50) NOT NULL DEFAULT '',
  `created_on` int(11) NOT NULL,
  `updated_on` int(11) NOT NULL,
  PRIMARY KEY (`id_soal_essay`),
  KEY `dosen_id` (`dosen_id`),
  KEY `matkul_id` (`matkul_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Master ujian essay (menu saat ini disembunyikan di sidebar)
CREATE TABLE IF NOT EXISTS `m_ujian_essay` (
  `id_ujian_essay` int(11) NOT NULL AUTO_INCREMENT,
  `dosen_id` int(11) NOT NULL,
  `matkul_id` int(11) NOT NULL,
  `nama_ujian_essay` varchar(200) NOT NULL,
  `jumlah_soal_essay` int(11) NOT NULL,
  `waktu_essay` int(11) NOT NULL,
  `jenis_essay` enum('acak','urut') NOT NULL,
  `tgl_mulai_essay` datetime NOT NULL,
  `tgl_akhir_essay` datetime NOT NULL,
  `token_essay` varchar(5) NOT NULL,
  PRIMARY KEY (`id_ujian_essay`),
  KEY `dosen_id` (`dosen_id`),
  KEY `matkul_id` (`matkul_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Hasil ujian essay
CREATE TABLE IF NOT EXISTS `h_ujian_essay` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ujian_id_essay` int(11) NOT NULL,
  `mahasiswa_id` int(11) NOT NULL,
  `list_soal_essay` longtext NOT NULL,
  `tgl_mulai` datetime NOT NULL,
  `tgl_selesai` datetime NOT NULL,
  `status` enum('Y','N') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ujian_id_essay` (`ujian_id_essay`),
  KEY `mahasiswa_id` (`mahasiswa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Group admin SPP (dipakai Users::is_spp)
INSERT INTO `groups` (`name`, `description`)
SELECT 'spp', 'Admin SPP' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `groups` WHERE `name` = 'spp');
