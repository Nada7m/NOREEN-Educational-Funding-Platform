<?php
session_start();

/* حذف جميع متغيرات الجلسة */
session_unset();

/* إنهاء الجلسة */
session_destroy();

/* إعادة المستخدم إلى صفحة تسجيل الدخول */
header("Location: Main Page.html");
exit();
?>