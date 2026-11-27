<?php

/**
 * Reusable navigation component.
 *
 * Optional profile fields:
 * initials, name, access_label, user_role, organisation, status
 * Missing values fall back to placeholders (AM / EP, Admin Name /
 * Employee Name, Location). The active item defaults to dashboard.
 */

$navigationConfig = $navigationConfig ?? [];

$role = $navigationConfig['role'] ?? '';
$activePage = $navigationConfig['active'] ?? 'dashboard';
$links = $navigationConfig['links'] ?? [];
$profile = $navigationConfig['profile'] ?? [];

if (!in_array($role, ['admin', 'employee'], true)) {
  return;
}

$escapeNavigation = static function ($value): string {
  return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$navigationIcons = [
  'dashboard' => 'assets/images/DashIcon.svg',
  'attendance' => 'assets/images/NavIcon.svg',
  'staff' => 'assets/images/StaffIcon.svg',
  'logout' => 'assets/images/LogIcon.svg',
];

$navigationItems = [
  'admin' => [
    'dashboard' => 'Dashboard',
    'attendance' => 'Attendance Log',
    'staff' => 'Staff Directory',
    'add_employee' => 'Add Employee',
  ],
  'employee' => [
    'dashboard' => 'Dashboard',
    'attendance' => 'Attendance Log',
  ],
];

$availableItems = [];

foreach ($navigationItems[$role] as $key => $label) {
  if (!empty($links[$key])) {
    $availableItems[$key] = [
      'label' => $label,
      'url' => $links[$key],
      'icon' => $navigationIcons[$key] ?? '',
    ];
  }
}

// Placeholder profile details until real login data is passed in.
$initials = $profile['initials'] ?? ($role === 'admin' ? 'AM' : 'EP');
$name = $profile['name'] ?? ($role === 'admin' ? 'Admin Name' : 'Employee Name');
$accessLabel = $profile['access_label'] ?? (
  $role === 'admin' ? 'Admin Access' : 'Employee Access'
);
$userRole = $profile['user_role'] ?? ucfirst($role);
$organisation = $profile['organisation'] ?? 'Location';
$status = $profile['status'] ?? 'Live';

?>

<div class="app-navigation app-navigation--<?php echo $escapeNavigation($role); ?>">

  <!-- Mobile profile control -->
  <div class="mobile-profile">

    <button
      type="button"
      class="mobile-profile__toggle"
      data-profile-toggle
      aria-expanded="false"
      aria-controls="profile-dropdown"
      aria-label="Open profile menu">

      <span class="mobile-profile__initials">
        <?php echo $escapeNavigation($initials); ?>
      </span>
    </button>

    <!-- Mobile profile dropdown -->
    <div
      class="profile-dropdown"
      id="profile-dropdown"
      data-profile-dropdown
      hidden>

      <section class="sidebar__access-card">

        <span class="sidebar__access-badge">
          <?php echo $escapeNavigation($accessLabel); ?>
        </span>

        <span class="sidebar__access-role">
          <?php echo $escapeNavigation(ucfirst($role)); ?>
        </span>

        <?php if ($organisation !== ''): ?>
          <span class="sidebar__organisation">
            <?php echo $escapeNavigation($organisation); ?>
          </span>
        <?php endif; ?>

        <?php if ($status !== ''): ?>
          <span class="sidebar__status">
            <span class="sidebar__status-dot"></span>
            <?php echo $escapeNavigation($status); ?>
          </span>
        <?php endif; ?>

      </section>

      <section class="sidebar__user-card">

        <span class="sidebar__user-initials">
          <?php echo $escapeNavigation($initials); ?>
        </span>

        <span class="sidebar__user-details">
          <span class="sidebar__user-name">
            <?php echo $escapeNavigation($name); ?>
          </span>

          <span class="sidebar__user-role">
            <?php echo $escapeNavigation($userRole); ?>
          </span>
        </span>

      </section>

      <?php if (!empty($links['logout'])): ?>
        <a
          href="<?php echo $escapeNavigation($links['logout']); ?>"
          class="profile-dropdown__logout">

          <img
            src="<?php echo $escapeNavigation($navigationIcons['logout']); ?>"
            alt=""
            aria-hidden="true"
            class="navigation-icon navigation-icon--logout">

          <span>LOGOUT</span>
        </a>
      <?php endif; ?>

    </div>
  </div>

  <!-- Desktop sidebar -->
  <aside class="sidebar" aria-label="Main navigation">

    <!-- Sidebar branding -->
    <a href="index.php" class="sidebar__brand" aria-label="LC Attends home">

      <img
        src="assets/images/LCLogo.svg"
        alt=""
        class="sidebar__logo">

      <img
        src="assets/images/LCAttends.svg"
        alt="LC Attends"
        class="sidebar__wordmark">

    </a>

    <!-- Desktop navigation links -->
    <nav class="sidebar__nav">

      <?php foreach ($availableItems as $key => $item): ?>

        <a
          href="<?php echo $escapeNavigation($item['url']); ?>"
          class="sidebar__link sidebar__link--<?php echo $escapeNavigation($key); ?><?php echo $activePage === $key ? ' sidebar__link--active' : ''; ?>"
          <?php echo $activePage === $key ? 'aria-current="page"' : ''; ?>>

          <?php if ($key === 'add_employee'): ?>

            <span
              class="navigation-icon navigation-icon--document"
              aria-hidden="true">🗎</span>

          <?php elseif (!empty($item['icon'])): ?>

            <img
              src="<?php echo $escapeNavigation($item['icon']); ?>"
              alt=""
              aria-hidden="true"
              class="navigation-icon">

          <?php endif; ?>

          <span class="sidebar__label">
            <?php echo $escapeNavigation($item['label']); ?>
          </span>

        </a>

      <?php endforeach; ?>

    </nav>

    <!-- Access information card -->
    <section class="sidebar__access-card">

      <span class="sidebar__access-badge">
        <?php echo $escapeNavigation($accessLabel); ?>
      </span>

      <span class="sidebar__access-role">
        <?php echo $escapeNavigation(ucfirst($role)); ?>
      </span>

      <?php if ($organisation !== ''): ?>
        <span class="sidebar__organisation">
          <?php echo $escapeNavigation($organisation); ?>
        </span>
      <?php endif; ?>

      <?php if ($status !== ''): ?>
        <span class="sidebar__status">
          <span class="sidebar__status-dot"></span>
          <?php echo $escapeNavigation($status); ?>
        </span>
      <?php endif; ?>

    </section>

    <!-- User information card -->
    <section class="sidebar__user-card">

      <span class="sidebar__user-initials">
        <?php echo $escapeNavigation($initials); ?>
      </span>

      <span class="sidebar__user-details">
        <span class="sidebar__user-name">
          <?php echo $escapeNavigation($name); ?>
        </span>

        <span class="sidebar__user-role">
          <?php echo $escapeNavigation($userRole); ?>
        </span>
      </span>

    </section>

    <!-- Desktop logout -->
    <?php if (!empty($links['logout'])): ?>

      <a
        href="<?php echo $escapeNavigation($links['logout']); ?>"
        class="sidebar__logout">

        <img
          src="<?php echo $escapeNavigation($navigationIcons['logout']); ?>"
          alt=""
          aria-hidden="true"
          class="navigation-icon navigation-icon--logout">

        <span>LOGOUT</span>

      </a>

    <?php endif; ?>

  </aside>

  <!-- Mobile bottom navigation: icons only -->
  <nav class="bottom-navigation" aria-label="Mobile navigation">

    <?php
    $mobileItems = $role === 'admin'
      ? ['dashboard', 'attendance', 'staff']
      : ['dashboard', 'attendance'];
    ?>

    <?php foreach ($mobileItems as $key): ?>

      <?php if (isset($availableItems[$key])): ?>

        <a
          href="<?php echo $escapeNavigation($availableItems[$key]['url']); ?>"
          class="bottom-navigation__link<?php echo $activePage === $key ? ' bottom-navigation__link--active' : ''; ?>"
          aria-label="<?php echo $escapeNavigation($availableItems[$key]['label']); ?>"
          <?php echo $activePage === $key ? 'aria-current="page"' : ''; ?>>

          <img
            src="<?php echo $escapeNavigation($availableItems[$key]['icon']); ?>"
            alt=""
            aria-hidden="true"
            class="navigation-icon">

        </a>

      <?php endif; ?>

    <?php endforeach; ?>

  </nav>

</div>