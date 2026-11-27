<?php

/**
 * Reusable application footer.
 *
 * The access label follows the logged-in role, taken from the same
 * $navigationConfig['role'] the navigation uses ('admin' or 'employee').
 * With no role (e.g. the login page) only the system name is shown.
 */

$footerRole = $navigationConfig['role'] ?? '';

$footerAccessLabels = [
  'admin' => 'Admin access only',
  'employee' => 'Employee access only',
];

$footerMeta = 'LC Attends System';

if (isset($footerAccessLabels[$footerRole])) {
  $footerMeta = $footerAccessLabels[$footerRole] . ' - ' . $footerMeta;
}
?>
<footer class="site-footer" aria-label="Site footer">
  <div class="site-footer__inner">
    <div class="site-footer__brand-col">
      <div class="site-footer__brand">
        <img
          src="./assets/images/LCLogo.svg"
          alt="Life Choices"
          class="site-footer__logo">
        <img
          src="./assets/images/LCAttends.svg"
          alt="LC Attends"
          class="site-footer__wordmark">
      </div>
      <p class="site-footer__mission">
        Life Choices invests in young people from all over South Africa, so every one of them has a fair chance in life.
      </p>
    </div>

    <div class="site-footer__contact-col">
      <h2 class="site-footer__heading">VISIT OR CONTACT US</h2>
      <address class="site-footer__details">
        <p class="site-footer__line">314 Imam Haron Rd, Lansdowne,<br>Cape Town, 7780</p>
        <p class="site-footer__line">
          <a href="tel:+27216964157" class="site-footer__link">+27 (0) 21 696 4157</a>
        </p>
        <p class="site-footer__line">
          <a href="mailto:theteam@lcstudio.co.za" class="site-footer__link">theteam@lcstudio.co.za</a>
        </p>
        <p class="site-footer__line">
          <a href="https://www.lcstudio.co.za" target="_blank" rel="noopener noreferrer" class="site-footer__link">www.lcstudio.co.za</a>
        </p>
      </address>
      <p class="site-footer__meta"><?php echo $footerMeta; ?></p>
    </div>
  </div>
</footer>