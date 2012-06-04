<?php
/** Der Name der Webapplikation													*/
define('SYS_NAME', 'nksarea');

/** Der Betreff der Einladungsmail												*/
define('SYS_MAIL_INVITE_SUBJECT', 'Einladung ins nksarea');

/** Template Datei für Einladungslink											*/
define('SYS_MAIL_INVITE_LINK', 'system/template/mail/invitelink');

/** Template Datei für Einladungsmail											*/
define('SYS_MAIL_INVITE_MAIL', 'system/template/mail/invite.mail.html');

/** Pfad zum temporären Ordner													*/
define('SYS_TMP', 'system/tmp');
define('SYS_TEMP_FOLDER', SYS_TMP);

/** Pfad zum Projekt-Ordner														*/
define('SYS_SHARE_PROJECTS', 'data/projects');

/** E-Mail Adresse, von der die Einladungen kommen								*/
define('SYS_NOREPLY', 'noreply@null.cedl.ch');

/** Trashordner																	*/
define('SYS_TRASH_FOLDER', 'system/trash/');

/** Löschfile																	*/
define('SYS_TRASH_FILE', 'system/trash/trash.csv');

define('SYS_PLUGIN_FOLDER', 'system/classes/plugins/');

/** Pfad zum Templateordner														*/
define('SYS_TEMPLATE_FOLDER', 'system/template/');

/** Pfad zum Ordner mit den UserInterface Templates								*/
define('SYS_UI_TMPL_DIR', 'system/template/html/');

/** Pfad zum Ordner mit den Standardfeldwerten									*/
define('SYS_FIELD_DIR', 'system/contents/fields/');

/** Pfad zum Ordner mit den aufrufbaren Seiten									*/
define('SYS_CNT_DIR', 'system/contents/pages/');

/** Pfad zum Ordner mit den Fehlermeldungsdateien								*/
define('SYS_ERR_DIR', 'system/contents/errors/');

// LIMITS
/** Mindestlänge eines Benutzernamens											*/
define('SYS_USERNAME_MINLENGTH', 3);

/** Maximallänge eines Benutzernamens											*/
define('SYS_USERNAME_MAXLENGTH', 20);

/** Mindestlänge des Passworts													*/
define('SYS_PASSWORD_MINLENGTH', 6);

/** Maximallänge des Passworts													*/
define('SYS_PASSWORD_MAXLENGTH', 50);

/** Maximallänge für Klassennamen												*/
define('SYS_CLASSNAME_MAXLENGTH', 6);

/** Maximallänge für Übernamen der Klassen										*/
define('SYS_CLASSNICK_MAXLENGTH', 24);

/**	Bei true werden Fehler mit trigger_error() ausgegeben						*/
define('TEST_MODE', true);