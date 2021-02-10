<!--

=========================================================
* Volt Free - Bootstrap 5 Dashboard
=========================================================

* Product Page: https://themesberg.com/product/admin-dashboard/volt-premium-bootstrap-5-dashboard
* Copyright 2020 Themesberg (https://www.themesberg.com)
* License (https://themes.getbootstrap.com/licenses/)

* Designed and coded by https://themesberg.com

=========================================================

* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software. Please contact us to request a removal.

-->
<!DOCTYPE html>
<html lang="<?= config('App')->defaultLocale ?>">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <!-- Primary Meta Tags -->
    <title>CodeIgniter4 Authentication</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?= csrf_meta() ?>

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon.ico">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="theme-color" content="#ffffff">

    <!-- Fontawesome -->
    <link type="text/css" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.2/css/all.min.css" rel="stylesheet">

    <!-- Volt CSS -->
    <link type="text/css" href="https://demo.themesberg.com/volt/css/volt.css" rel="stylesheet">
</head>
<body>
    <main>
        <!-- Section -->
        <?= $this->renderSection('content') ?>
    </main>

    <!-- Core -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.min.js"></script>

    <!-- Volt JS -->
    <script>"use strict";const d=document;d.addEventListener("DOMContentLoaded",function(e){var t=document.getElementById("theme-settings"),o=document.getElementById("theme-settings-expand");if(t){var n=new bootstrap.Collapse(t,{show:!0,toggle:!1});"true"===window.localStorage.getItem("settings_expanded")?(n.show(),o.classList.remove("show")):(n.hide(),o.classList.add("show")),t.addEventListener("hidden.bs.collapse",function(){o.classList.add("show"),window.localStorage.setItem("settings_expanded",!1)}),o.addEventListener("click",function(){o.classList.remove("show"),window.localStorage.setItem("settings_expanded",!0),setTimeout(function(){n.show()},300)})}const l=960;var a=document.getElementById("sidebarMenu");a&&d.body.clientWidth<l&&(a.addEventListener("shown.bs.collapse",function(){document.querySelector("body").style.position="fixed"}),a.addEventListener("hidden.bs.collapse",function(){document.querySelector("body").style.position="relative"})),[].slice.call(d.querySelectorAll("[data-background]")).map(function(e){e.style.background="url("+e.getAttribute("data-background")+")"}),[].slice.call(d.querySelectorAll("[data-background-lg]")).map(function(e){document.body.clientWidth>l&&(e.style.background="url("+e.getAttribute("data-background-lg")+")")}),[].slice.call(d.querySelectorAll("[data-background-color]")).map(function(e){e.style.background="url("+e.getAttribute("data-background-color")+")"}),[].slice.call(d.querySelectorAll("[data-color]")).map(function(e){e.style.color="url("+e.getAttribute("data-color")+")"})});</script>
</body>
</html>