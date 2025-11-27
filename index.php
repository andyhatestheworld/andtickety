<?php
require_once __DIR__ . '/include/configuration/config.php';
?>

<!DOCTYPE html>

<html lang="en">

	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title><?= $site_name ?></title>
		<link rel="icon" type="image/x-icon" href="assets/favicon.ico">


		<meta name="robots" content="index, follow">
		<link rel="canonical" href="<?= $site_url ?>">
		<meta name="description" content="<?= $site_description ?>">

		<meta property="og:title" content="<?= $site_name ?>">
		<meta property="og:description" content="<?= $site_description ?>">
		<meta property="og:type" content="website">
		<meta property="og:url" content="<?= $site_url ?>">
		<meta property="og:image" content="<?= $site_url ?>/assets/images/logo.png">


		<meta name="twitter:card" content="summary_large_image">
		<meta name="twitter:title" content="<?= $site_name ?>">
		<meta name="twitter:description" content="<?= $site_description ?>">
		<meta name="twitter:image" content="<?= $site_url ?>/assets/images/logo.png">

		<link rel="stylesheet" href="assets/index.css">
		<link rel="stylesheet" href="assets/glightbox-3.3.0/dist/css/glightbox.min.css" />
	</head>

	<body>
		<script src="assets/glightbox-3.3.0/dist/js/glightbox.min.js"></script>
		<script>
			document.addEventListener('DOMContentLoaded', function() {
				const lightbox = GLightbox({
					selector: '.glightbox'
				});
			});
		</script>

		<?php include 'include/navbar.php'; ?>

		<div class="container">
			<div class="info-box">
				<?php
					$page = $_GET['page'] ?? 'home';
					$file = "pages/$page.php";
					if (file_exists($file)) {
						include $file;
					} else {
						$file = "pages/home.php";
						include $file;
					}
				?>
			</div>
		</div>

		<!-- <?php include 'include/footer.php'; ?> -->


	</body>

</html>

