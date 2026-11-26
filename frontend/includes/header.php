<?php

/**
 * Reusable application header.
 *
 * The brand bar is shown on mobile and on desktop pages without the sidebar
 * (see layout.css). The link label is the accessible name, so the images
 * are decorative.
 */
?>

<header class="site-header">
  <div class="site-header__container">
    <a href="index.php" class="site-header__brand" aria-label="LC Attends home">
      <img
        src="assets/images/LCLogo.svg"
        alt=""
        width="101"
        height="60"
        class="site-header__logo">

      <img
        src="assets/images/LCAttends.svg"
        alt=""
        width="110"
        height="15"
        class="site-header__wordmark">
    </a>
  </div>
</header>