/**
 * Mobile profile dropdown toggle.
 */
document.addEventListener("DOMContentLoaded", function () {
  const profileToggle = document.querySelector("[data-profile-toggle]");
  const profileDropdown = document.querySelector("[data-profile-dropdown]");

  if (!profileToggle || !profileDropdown) {
    return;
  }

  profileToggle.addEventListener("click", function () {
    const isExpanded = profileToggle.getAttribute("aria-expanded") === "true";

    profileToggle.setAttribute("aria-expanded", String(!isExpanded));
    profileDropdown.hidden = isExpanded;
  });

  // Close the dropdown when clicking outside it.
  document.addEventListener("click", function (event) {
    const clickedInside =
      profileToggle.contains(event.target) ||
      profileDropdown.contains(event.target);

    if (!clickedInside) {
      profileToggle.setAttribute("aria-expanded", "false");
      profileDropdown.hidden = true;
    }
  });

  // Close the dropdown with the Escape key.
  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
      profileToggle.setAttribute("aria-expanded", "false");
      profileDropdown.hidden = true;
      profileToggle.focus();
    }
  });
});
