function renderStatusBadge(status) {
  const el = document.getElementById("status-badge");
  const isIn = status === "IN";
  el.className = "status-badge " + (isIn ? "status-badge--in" : "status-badge--out");
  el.textContent = isIn ? "IN" : "OUT";
}

// Dummy data for now, Imaan will pass in the real value
renderStatusBadge("IN");