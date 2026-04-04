<?php
// templates/header.php
if (!isset($pageTitle)) {
    $pageTitle = 'Mensa';
}
?>
<!DOCTYPE html>
<html>
<head>
<title><?php echo h($pageTitle); ?></title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="./style.css">
</head>
<body class="modern-dark">

<!-- Icon Bar (Sidebar - hidden on small screens) -->
<nav class="w3-sidebar modern-sidebar w3-hide-small w3-center">
  <!-- Avatar image in top left corner -->
  <div class="sidebar-avatar">
    <img src="images/croissant.jpg" alt="Mensa Logo">
  </div>

  
  <?php if (isset($sidebarHtml)): ?>
      <?php echo $sidebarHtml; ?>
  <?php endif; ?>
</nav>

<!-- Navbar on small screens (Hidden on medium and large screens) -->
<div class="w3-top w3-hide-large w3-hide-medium" id="myNavbar">
  <div class="w3-bar w3-black w3-opacity w3-hover-opacity-off w3-center w3-small">
    <?php if (isset($navbarSmallHtml)): ?>
        <?php echo $navbarSmallHtml; ?>
    <?php endif; ?>
  </div>
</div>

<!-- Page Content -->
<div id="main">

<script>
function openTab(tabId, event, scrollTargetId) {
  if (event) {
    event.preventDefault();
  }
  
  // Hide all elements with class="tab-content"
  var i, tabcontent, tablinks;
  tabcontent = document.getElementsByClassName("tab-content");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].classList.remove("active");
  }

  // Remove the class "active" from all tablinks
  tablinks = document.querySelectorAll(".modern-sidebar a.w3-bar-item, #myNavbar a.w3-bar-item");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].classList.remove("active");
  }

  // Show the current tab
  var targetTab = document.getElementById(tabId);
  if (targetTab) {
    targetTab.classList.add("active");
    // Default scroll to top if no target specificied
    if (!scrollTargetId) window.scrollTo(0, 0);
  }

  // Handle Internal Section Scroll
  if (scrollTargetId) {
    var sectionTarget = document.getElementById(scrollTargetId);
    if (sectionTarget) {
      setTimeout(function() {
         sectionTarget.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }, 100);
    }
  }

  // Add active class to corresponding sidebar/navbar items
  // We match by the tabId primarily
  var links = document.querySelectorAll('a[onclick*="openTab(\'' + tabId + '\'"]');
  links.forEach(function(link) {
    // If we have a scroll target, only match links that mention it
    if (scrollTargetId) {
       if (link.getAttribute('onclick').indexOf(scrollTargetId) !== -1) {
          link.classList.add("active");
       }
    } else {
       link.classList.add("active");
    }
  });

  // Update URL hash without jumping
  var hash = scrollTargetId ? tabId + '-' + scrollTargetId : tabId;
  if (history.pushState) {
    history.pushState(null, null, '#' + hash);
  } else {
    location.hash = '#' + hash;
  }
}

// Handle initial load and browser back/forward
window.addEventListener('load', function() {
  var hash = window.location.hash.substring(1);
  if (hash) {
    var parts = hash.split('-');
    if (parts.length > 1) {
      openTab(parts[0], null, parts[1]);
    } else {
      openTab(parts[0]);
    }
  } else {
    // Default to the first available tab-content
    var firstTab = document.querySelector('.tab-content');
    if (firstTab) {
      openTab(firstTab.id);
    }
  }
});

window.addEventListener('hashchange', function() {
  var hash = window.location.hash.substring(1);
  if (hash) {
    var parts = hash.split('-');
    if (parts.length > 1) {
      openTab(parts[0], null, parts[1]);
    } else {
      openTab(parts[0]);
    }
  }
});
</script>
