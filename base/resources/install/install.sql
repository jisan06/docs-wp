CREATE TABLE IF NOT EXISTS `#__easydoc_documents` (
  `easydoc_document_id` BIGINT(20)  UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
  `uuid` char(36) NOT NULL UNIQUE,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL UNIQUE,
  `easydoc_category_id` bigint(20) UNSIGNED NOT NULL,
  `description` longtext,
  `image` varchar(512) NOT NULL default '',
  `storage_type` varchar(64) NOT NULL default '',
  `storage_path` varchar(512) NOT NULL default '',
  `hits` int(11) NOT NULL default 0,
  `enabled` tinyint(1) NOT NULL default 1,
  `publish_on` datetime DEFAULT NULL,
  `unpublish_on` datetime DEFAULT NULL,
  `locked_on` datetime DEFAULT NULL,
  `locked_by` bigint(20) NOT NULL default 0,
  `created_on` datetime DEFAULT NULL,
  `created_by` bigint(20) NOT NULL default 0,
  `modified_on` datetime DEFAULT NULL,
  `modified_by` bigint(20) NOT NULL default 0,
  `params` text,
  `asset_id` INTEGER UNSIGNED NOT NULL DEFAULT 0,
	`ordering` int(11) NOT NULL default 0,
  KEY `category_index` (`easydoc_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__easydoc_usergroups` (
    `easydoc_usergroup_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL DEFAULT '',
    `description` text,
    `created_by` bigint(20) unsigned DEFAULT NULL,
    `created_on` datetime NOT NULL,
    `modified_by` bigint(20) unsigned NOT NULL,
    `modified_on` datetime NOT NULL,
    `internal` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`easydoc_usergroup_id`),
  UNIQUE KEY `name` (`name`,`internal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__easydoc_usergroups_users` (
  `easydoc_usergroup_id` bigint(20) unsigned NOT NULL,
  `wp_user_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`easydoc_usergroup_id`,`wp_user_id`),
  KEY `wp_user_id` (`wp_user_id`),
  CONSTRAINT `#__easydoc_usergroups_users_ibfk_1` FOREIGN KEY (`easydoc_usergroup_id`) REFERENCES `#__easydoc_usergroups` (`easydoc_usergroup_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__easydoc_categories` (
    `easydoc_category_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
  	`uuid` char(36) NOT NULL UNIQUE,
    `title` varchar(255) NOT NULL,
    `slug` varchar(255) NOT NULL UNIQUE,
    `description` text,
    `image` varchar(512) NOT NULL default '',
    `params` text,
    `inherit_category_group_access` bigint(20) unsigned DEFAULT NULL,
    `inherit_document_group_access` bigint(20) unsigned DEFAULT NULL,
    `inherit_permissions` bigint(20) unsigned DEFAULT NULL,
    `enabled` tinyint(1) NOT NULL default 1,
    `locked_on` datetime DEFAULT NULL,
    `locked_by` bigint(20) NOT NULL default 0,
    `created_on` datetime DEFAULT NULL,
    `created_by` bigint(20) NOT NULL default 0,
    `modified_on` datetime DEFAULT NULL,
    `modified_by` bigint(20) NOT NULL default 0,
    `asset_id` INTEGER UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__easydoc_categories_permissions` (
  `easydoc_category_id` bigint(20) unsigned NOT NULL,
  `action` tinyint(3) unsigned NOT NULL,
  `wp_user_id` bigint(32) unsigned NOT NULL,
	`allowed` tinyint(1) NOT NULL,
  UNIQUE KEY `category_action_user` (`easydoc_category_id`,`action`,`wp_user_id`),
  KEY `action_user` (`action`, `wp_user_id`),
  KEY `user` (`wp_user_id`),
  KEY `action` (`action`),
  KEY `category` (`easydoc_category_id`),
	KEY `allowed` (`allowed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__easydoc_users` (
  `wp_user_id` bigint(32) NOT NULL,
  `permissions_map` text,
  `roles_hash` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`wp_user_id`),
  KEY `#__easydoc_users_wp_user_id_roles_hash_idx` (`roles_hash`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__easydoc_permissions` (
  `easydoc_permission_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `table` varchar(255) NOT NULL DEFAULT '',
  `row` bigint(20) unsigned NOT NULL,
  `data` text,
  PRIMARY KEY (`easydoc_permission_id`),
  UNIQUE KEY `table` (`table`,`row`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__easydoc_category_group_access` (
    `easydoc_category_id` bigint(20) unsigned NOT NULL,
    `easydoc_usergroup_id` bigint(20) NOT NULL,
    PRIMARY KEY (`easydoc_category_id`,`easydoc_usergroup_id`),
    KEY `easydoc_usergroup_id` (`easydoc_usergroup_id`),
    CONSTRAINT `#__easydoc_category_group_access_ibfk_1` FOREIGN KEY (`easydoc_category_id`) REFERENCES `#__easydoc_categories` (`easydoc_category_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__easydoc_document_group_access` (
    `easydoc_category_id` bigint(20) unsigned NOT NULL,
    `easydoc_usergroup_id` bigint(20) NOT NULL,
    PRIMARY KEY (`easydoc_category_id`,`easydoc_usergroup_id`),
    KEY `easydoc_usergroup_id` (`easydoc_usergroup_id`),
    CONSTRAINT `#__easydoc_document_group_access_ibfk_1` FOREIGN KEY (`easydoc_category_id`) REFERENCES `#__easydoc_categories` (`easydoc_category_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__easydoc_category_relations` (
  `ancestor_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `descendant_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `level` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ancestor_id`, `descendant_id`, `level`),
  KEY `path_index` (`descendant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__easydoc_category_orderings` (
  `easydoc_category_id` bigint(20) unsigned NOT NULL,
  `title` int(11) NOT NULL DEFAULT '0',
  `custom` int(11) NOT NULL DEFAULT '0',
  `created_on` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`easydoc_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__easydoc_category_folders` (
  `easydoc_category_id` bigint(20) unsigned NOT NULL,
  `folder` varchar(4096) NOT NULL DEFAULT '',
  `automatic` tinyint(1) NOT NULL DEFAULT '0',
  UNIQUE KEY `easydoc_category_id` (`easydoc_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__easydoc_files` (
  `easydoc_file_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `folder` varchar(2048) NOT NULL DEFAULT '',
  `name` varchar(2048) NOT NULL DEFAULT '',
  `modified_on` datetime DEFAULT NULL,
  `modified_by` bigint(20) NOT NULL,
  `created_on` datetime DEFAULT NULL,
  `created_by` bigint(20) NOT NULL,
  `parameters` text,
  PRIMARY KEY (`easydoc_file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__easydoc_folders` (
  `easydoc_folder_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `folder` varchar(2048) NOT NULL DEFAULT '',
  `name` varchar(2048) NOT NULL DEFAULT '',
  `modified_on` datetime DEFAULT NULL,
  `modified_by` bigint(20) NOT NULL,
  `created_on` datetime DEFAULT NULL,
  `created_by` bigint(20) NOT NULL,
  `parameters` text,
  PRIMARY KEY (`easydoc_folder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__easydoc_tags` (
  `tag_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
	`count` int(11) DEFAULT '0',
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_on` datetime DEFAULT NULL,
  `modified_by` int(10) UNSIGNED DEFAULT NULL,
  `modified_on` datetime DEFAULT NULL,
  `locked_by` int(10) UNSIGNED DEFAULT NULL,
  `locked_on` datetime DEFAULT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`tag_id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__easydoc_tags_relations` (
  `tag_id` bigint(20) UNSIGNED NOT NULL,
  `row` varchar(36) NOT NULL,
  PRIMARY KEY (`tag_id`, `row`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__easydoc_scans` (
  `easydoc_scan_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
  `identifier` varchar(64) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `ocr` tinyint(1) NOT NULL DEFAULT '0',
  `thumbnail` tinyint(1) NOT NULL DEFAULT '0',
  `modified_on` datetime DEFAULT NULL,
  `created_on` datetime DEFAULT NULL,
  `response` varchar(2048) NOT NULL DEFAULT '',
  `sent_on` datetime DEFAULT NULL,
  `retries` tinyint(1) unsigned DEFAULT 0,
  `parameters` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__easydoc_routes` (
  `uuid` char(36) NOT NULL,
  `type` varchar(64) NOT NULL DEFAULT '',
  `path` varchar(2048) NOT NULL DEFAULT '',
  UNIQUE KEY `uuid` (`uuid`),
  KEY `path_idx` (`path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__easydoc_document_contents` (
  `easydoc_document_id` bigint(20) unsigned NOT NULL,
  `contents` longtext,
  UNIQUE KEY `easydoc_document_id` (`easydoc_document_id`),
  FOREIGN KEY (`easydoc_document_id`) REFERENCES `#__easydoc_documents`(`easydoc_document_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__easydoc_notifications` (
  `easydoc_notification_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `row` bigint(20) unsigned NOT NULL,
  `table` varchar(255) NOT NULL,
  `description` text,
  `notifier` varchar(255) NOT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_by` bigint(20) unsigned NOT NULL,
  `modified_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `inheritable` tinyint(1) DEFAULT '0',
  `parameters` text NOT NULL,
  PRIMARY KEY (`easydoc_notification_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS  `#__easydoc_emails` (
  `easydoc_email_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `easydoc_notification_id` bigint(20) unsigned NOT NULL,
  `recipient` varchar(320) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `subject` text,
  `body` text,
  `status` tinyint(1) DEFAULT '0',
  `created_on` datetime DEFAULT NULL,
  `sent_on` datetime DEFAULT NULL,
  `retries` tinyint DEFAULT '0',
  `parameters` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  PRIMARY KEY (`easydoc_email_id`),
  KEY `easydoc_emails_recipient_idx` (`recipient`) USING BTREE,
  KEY `easydoc_notification_id` (`easydoc_notification_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__files_mimetypes` (
                                                    `mimetype` VARCHAR(255) NOT NULL,
    `extension` VARCHAR(64) NOT NULL,
    PRIMARY KEY (`mimetype`, `extension`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__files_containers` (
                                                     `files_container_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `slug` varchar(255) NOT NULL,
    `title` varchar(255) NOT NULL,
    `path` varchar(255) NOT NULL,
    `parameters` text NOT NULL,
    PRIMARY KEY (`files_container_id`),
    UNIQUE KEY `slug` (`slug`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/andrew-inset', 'ez'), ('application/applixware', 'aw'), ('application/atom+xml', 'atom'), ('application/atomcat+xml', 'atomcat'), ('application/atomsvc+xml', 'atomsvc'), ('application/ccxml+xml', 'ccxml'), ('application/cdmi-capability', 'cdmia'), ('application/cdmi-container', 'cdmic'), ('application/cdmi-domain', 'cdmid'), ('application/cdmi-object', 'cdmio'), ('application/cdmi-queue', 'cdmiq'), ('application/cu-seeme', 'cu'), ('application/davmount+xml', 'davmount'), ('application/docbook+xml', 'dbk'), ('application/dssc+der', 'dssc'), ('application/dssc+xml', 'xdssc'), ('application/ecmascript', 'ecma'), ('application/emma+xml', 'emma'), ('application/epub+zip', 'epub'), ('application/exi', 'exi');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/font-tdpfr', 'pfr'), ('application/gml+xml', 'gml'), ('application/gpx+xml', 'gpx'), ('application/gxf', 'gxf'), ('application/hyperstudio', 'stk'), ('application/inkml+xml', 'ink'), ('application/inkml+xml', 'inkml'), ('application/ipfix', 'ipfix'), ('application/java-archive', 'jar'), ('application/java-serialized-object', 'ser'), ('application/java-vm', 'class'), ('application/javascript', 'js'), ('application/json', 'json'), ('application/jsonml+json', 'jsonml'), ('application/lost+xml', 'lostxml'), ('application/mac-binhex40', 'hqx'), ('application/mac-compactpro', 'cpt'), ('application/mads+xml', 'mads'), ('application/marc', 'mrc');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/marcxml+xml', 'mrcx'), ('application/mathematica', 'ma'), ('application/mathematica', 'nb'), ('application/mathematica', 'mb'), ('application/mathml+xml', 'mathml'), ('application/mbox', 'mbox'), ('application/mediaservercontrol+xml', 'mscml'), ('application/metalink+xml', 'metalink'), ('application/metalink4+xml', 'meta4'), ('application/mets+xml', 'mets'), ('application/mods+xml', 'mods'), ('application/mp21', 'm21'), ('application/mp21', 'mp21'), ('application/mp4', 'mp4s'), ('application/msword', 'doc'), ('application/msword', 'dot'), ('application/mxf', 'mxf'), ('application/octet-stream', 'bin'), ('application/octet-stream', 'dms');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/octet-stream', 'lrf'), ('application/octet-stream', 'mar'), ('application/octet-stream', 'so'), ('application/octet-stream', 'dist'), ('application/octet-stream', 'distz'), ('application/octet-stream', 'pkg'), ('application/octet-stream', 'bpk'), ('application/octet-stream', 'dump'), ('application/octet-stream', 'elc'), ('application/octet-stream', 'deploy'), ('application/oda', 'oda'), ('application/oebps-package+xml', 'opf'), ('application/ogg', 'ogx'), ('application/omdoc+xml', 'omdoc'), ('application/onenote', 'onetoc'), ('application/onenote', 'onetoc2'), ('application/onenote', 'onetmp'), ('application/onenote', 'onepkg'), ('application/oxps', 'oxps');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/patch-ops-error+xml', 'xer'), ('application/pdf', 'pdf'), ('application/pgp-encrypted', 'pgp'), ('application/pgp-signature', 'asc'), ('application/pgp-signature', 'sig'), ('application/pics-rules', 'prf'), ('application/pkcs10', 'p10'), ('application/pkcs7-mime', 'p7m'), ('application/pkcs7-mime', 'p7c'), ('application/pkcs7-signature', 'p7s'), ('application/pkcs8', 'p8'), ('application/pkix-attr-cert', 'ac'), ('application/pkix-cert', 'cer'), ('application/pkix-crl', 'crl'), ('application/pkix-pkipath', 'pkipath'), ('application/pkixcmp', 'pki'), ('application/pls+xml', 'pls'), ('application/postscript', 'ai'), ('application/postscript', 'eps');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/postscript', 'ps'), ('application/prs.cww', 'cww'), ('application/pskc+xml', 'pskcxml'), ('application/rdf+xml', 'rdf'), ('application/reginfo+xml', 'rif'), ('application/relax-ng-compact-syntax', 'rnc'), ('application/resource-lists+xml', 'rl'), ('application/resource-lists-diff+xml', 'rld'), ('application/rls-services+xml', 'rs'), ('application/rpki-ghostbusters', 'gbr'), ('application/rpki-manifest', 'mft'), ('application/rpki-roa', 'roa'), ('application/rsd+xml', 'rsd'), ('application/rss+xml', 'rss'), ('application/rtf', 'rtf'), ('application/sbml+xml', 'sbml'), ('application/scvp-cv-request', 'scq'), ('application/scvp-cv-response', 'scs'), ('application/scvp-vp-request', 'spq');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/scvp-vp-response', 'spp'), ('application/sdp', 'sdp'), ('application/set-payment-initiation', 'setpay'), ('application/set-registration-initiation', 'setreg'), ('application/shf+xml', 'shf'), ('application/smil+xml', 'smi'), ('application/smil+xml', 'smil'), ('application/sparql-query', 'rq'), ('application/sparql-results+xml', 'srx'), ('application/srgs', 'gram'), ('application/srgs+xml', 'grxml'), ('application/sru+xml', 'sru'), ('application/ssdl+xml', 'ssdl'), ('application/ssml+xml', 'ssml'), ('application/tei+xml', 'tei'), ('application/tei+xml', 'teicorpus'), ('application/thraud+xml', 'tfi'), ('application/timestamped-data', 'tsd'), ('application/vnd.3gpp.pic-bw-large', 'plb');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/vnd.3gpp.pic-bw-small', 'psb'), ('application/vnd.3gpp.pic-bw-var', 'pvb'), ('application/vnd.3gpp2.tcap', 'tcap'), ('application/vnd.3m.post-it-notes', 'pwn'), ('application/vnd.accpac.simply.aso', 'aso'), ('application/vnd.accpac.simply.imp', 'imp'), ('application/vnd.acucobol', 'acu'), ('application/vnd.acucorp', 'atc'), ('application/vnd.acucorp', 'acutc'), ('application/vnd.adobe.air-application-installer-package+zip', 'air'), ('application/vnd.adobe.formscentral.fcdt', 'fcdt'), ('application/vnd.adobe.fxp', 'fxp'), ('application/vnd.adobe.fxp', 'fxpl'), ('application/vnd.adobe.xdp+xml', 'xdp'), ('application/vnd.adobe.xfdf', 'xfdf'), ('application/vnd.ahead.space', 'ahead'), ('application/vnd.airzip.filesecure.azf', 'azf'), ('application/vnd.airzip.filesecure.azs', 'azs'), ('application/vnd.amazon.ebook', 'azw');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/vnd.americandynamics.acc', 'acc'), ('application/vnd.amiga.ami', 'ami'), ('application/vnd.android.package-archive', 'apk'), ('application/vnd.anser-web-certificate-issue-initiation', 'cii'), ('application/vnd.anser-web-funds-transfer-initiation', 'fti'), ('application/vnd.antix.game-component', 'atx'), ('application/vnd.apple.installer+xml', 'mpkg'), ('application/vnd.apple.mpegurl', 'm3u8'), ('application/vnd.aristanetworks.swi', 'swi'), ('application/vnd.astraea-software.iota', 'iota'), ('application/vnd.audiograph', 'aep'), ('application/vnd.blueice.multipass', 'mpm'), ('application/vnd.bmi', 'bmi'), ('application/vnd.businessobjects', 'rep'), ('application/vnd.chemdraw+xml', 'cdxml'), ('application/vnd.chipnuts.karaoke-mmd', 'mmd'), ('application/vnd.cinderella', 'cdy'), ('application/vnd.claymore', 'cla'), ('application/vnd.cloanto.rp9', 'rp9');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/vnd.clonk.c4group', 'c4g'), ('application/vnd.clonk.c4group', 'c4d'), ('application/vnd.clonk.c4group', 'c4f'), ('application/vnd.clonk.c4group', 'c4p'), ('application/vnd.clonk.c4group', 'c4u'), ('application/vnd.cluetrust.cartomobile-config', 'c11amc'), ('application/vnd.cluetrust.cartomobile-config-pkg', 'c11amz'), ('application/vnd.commonspace', 'csp'), ('application/vnd.contact.cmsg', 'cdbcmsg'), ('application/vnd.cosmocaller', 'cmc'), ('application/vnd.crick.clicker', 'clkx'), ('application/vnd.crick.clicker.keyboard', 'clkk'), ('application/vnd.crick.clicker.palette', 'clkp'), ('application/vnd.crick.clicker.template', 'clkt'), ('application/vnd.crick.clicker.wordbank', 'clkw'), ('application/vnd.criticaltools.wbs+xml', 'wbs'), ('application/vnd.ctc-posml', 'pml'), ('application/vnd.cups-ppd', 'ppd'), ('application/vnd.curl.car', 'car');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/vnd.curl.pcurl', 'pcurl'), ('application/vnd.dart', 'dart'), ('application/vnd.data-vision.rdz', 'rdz'), ('application/vnd.dece.data', 'uvf'), ('application/vnd.dece.data', 'uvvf'), ('application/vnd.dece.data', 'uvd'), ('application/vnd.dece.data', 'uvvd'), ('application/vnd.dece.ttml+xml', 'uvt'), ('application/vnd.dece.ttml+xml', 'uvvt'), ('application/vnd.dece.unspecified', 'uvx'), ('application/vnd.dece.unspecified', 'uvvx'), ('application/vnd.dece.zip', 'uvz'), ('application/vnd.dece.zip', 'uvvz'), ('application/vnd.denovo.fcselayout-link', 'fe_launch'), ('application/vnd.dna', 'dna'), ('application/vnd.dolby.mlp', 'mlp'), ('application/vnd.dpgraph', 'dpg'), ('application/vnd.dreamfactory', 'dfac'), ('application/vnd.ds-keypoint', 'kpxx');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/vnd.dvb.ait', 'ait'), ('application/vnd.dvb.service', 'svc'), ('application/vnd.dynageo', 'geo'), ('application/vnd.ecowin.chart', 'mag'), ('application/vnd.enliven', 'nml'), ('application/vnd.epson.esf', 'esf'), ('application/vnd.epson.msf', 'msf'), ('application/vnd.epson.quickanime', 'qam'), ('application/vnd.epson.salt', 'slt'), ('application/vnd.epson.ssf', 'ssf'), ('application/vnd.eszigno3+xml', 'es3'), ('application/vnd.eszigno3+xml', 'et3'), ('application/vnd.ezpix-album', 'ez2'), ('application/vnd.ezpix-package', 'ez3'), ('application/vnd.fdf', 'fdf'), ('application/vnd.fdsn.mseed', 'mseed'), ('application/vnd.fdsn.seed', 'seed'), ('application/vnd.fdsn.seed', 'dataless'), ('application/vnd.flographit', 'gph');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/vnd.fluxtime.clip', 'ftc'), ('application/vnd.framemaker', 'fm'), ('application/vnd.framemaker', 'frame'), ('application/vnd.framemaker', 'maker'), ('application/vnd.framemaker', 'book'), ('application/vnd.frogans.fnc', 'fnc'), ('application/vnd.frogans.ltf', 'ltf'), ('application/vnd.fsc.weblaunch', 'fsc'), ('application/vnd.fujitsu.oasys', 'oas'), ('application/vnd.fujitsu.oasys2', 'oa2'), ('application/vnd.fujitsu.oasys3', 'oa3'), ('application/vnd.fujitsu.oasysgp', 'fg5'), ('application/vnd.fujitsu.oasysprs', 'bh2'), ('application/vnd.fujixerox.ddd', 'ddd'), ('application/vnd.fujixerox.docuworks', 'xdw'), ('application/vnd.fujixerox.docuworks.binder', 'xbd'), ('application/vnd.fuzzysheet', 'fzs'), ('application/vnd.genomatix.tuxedo', 'txd'), ('application/vnd.geogebra.file', 'ggb');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/vnd.geogebra.tool', 'ggt'), ('application/vnd.geometry-explorer', 'gex'), ('application/vnd.geometry-explorer', 'gre'), ('application/vnd.geonext', 'gxt'), ('application/vnd.geoplan', 'g2w'), ('application/vnd.geospace', 'g3w'), ('application/vnd.gmx', 'gmx'), ('application/vnd.google-earth.kml+xml', 'kml'), ('application/vnd.google-earth.kmz', 'kmz'), ('application/vnd.grafeq', 'gqf'), ('application/vnd.grafeq', 'gqs'), ('application/vnd.groove-account', 'gac'), ('application/vnd.groove-help', 'ghf'), ('application/vnd.groove-identity-message', 'gim'), ('application/vnd.groove-injector', 'grv'), ('application/vnd.groove-tool-message', 'gtm'), ('application/vnd.groove-tool-template', 'tpl'), ('application/vnd.groove-vcard', 'vcg'), ('application/vnd.hal+xml', 'hal');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/vnd.handheld-entertainment+xml', 'zmm'), ('application/vnd.hbci', 'hbci'), ('application/vnd.hhe.lesson-player', 'les'), ('application/vnd.hp-hpgl', 'hpgl'), ('application/vnd.hp-hpid', 'hpid'), ('application/vnd.hp-hps', 'hps'), ('application/vnd.hp-jlyt', 'jlt'), ('application/vnd.hp-pcl', 'pcl'), ('application/vnd.hp-pclxl', 'pclxl'), ('application/vnd.hydrostatix.sof-data', 'sfd-hdstx'), ('application/vnd.ibm.minipay', 'mpy'), ('application/vnd.ibm.modcap', 'afp'), ('application/vnd.ibm.modcap', 'listafp'), ('application/vnd.ibm.modcap', 'list3820'), ('application/vnd.ibm.rights-management', 'irm'), ('application/vnd.ibm.secure-container', 'sc'), ('application/vnd.iccprofile', 'icc'), ('application/vnd.iccprofile', 'icm'), ('application/vnd.igloader', 'igl');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/vnd.immervision-ivp', 'ivp'), ('application/vnd.immervision-ivu', 'ivu'), ('application/vnd.insors.igm', 'igm'), ('application/vnd.intercon.formnet', 'xpw'), ('application/vnd.intercon.formnet', 'xpx'), ('application/vnd.intergeo', 'i2g'), ('application/vnd.intu.qbo', 'qbo'), ('application/vnd.intu.qfx', 'qfx'), ('application/vnd.ipunplugged.rcprofile', 'rcprofile'), ('application/vnd.irepository.package+xml', 'irp'), ('application/vnd.is-xpr', 'xpr'), ('application/vnd.isac.fcs', 'fcs'), ('application/vnd.jam', 'jam'), ('application/vnd.jcp.javame.midlet-rms', 'rms'), ('application/vnd.jisp', 'jisp'), ('application/vnd.joost.joda-archive', 'joda'), ('application/vnd.kahootz', 'ktz'), ('application/vnd.kahootz', 'ktr'), ('application/vnd.kde.karbon', 'karbon');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/vnd.kde.kchart', 'chrt'), ('application/vnd.kde.kformula', 'kfo'), ('application/vnd.kde.kivio', 'flw'), ('application/vnd.kde.kontour', 'kon'), ('application/vnd.kde.kpresenter', 'kpr'), ('application/vnd.kde.kpresenter', 'kpt'), ('application/vnd.kde.kspread', 'ksp'), ('application/vnd.kde.kword', 'kwd'), ('application/vnd.kde.kword', 'kwt'), ('application/vnd.kenameaapp', 'htke'), ('application/vnd.kidspiration', 'kia'), ('application/vnd.kinar', 'kne'), ('application/vnd.kinar', 'knp'), ('application/vnd.koan', 'skp'), ('application/vnd.koan', 'skd'), ('application/vnd.koan', 'skt'), ('application/vnd.koan', 'skm'), ('application/vnd.kodak-descriptor', 'sse'), ('application/vnd.las.las+xml', 'lasxml');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/vnd.llamagraphics.life-balance.desktop', 'lbd'), ('application/vnd.llamagraphics.life-balance.exchange+xml', 'lbe'), ('application/vnd.lotus-1-2-3', '123'), ('application/vnd.lotus-approach', 'apr'), ('application/vnd.lotus-freelance', 'pre'), ('application/vnd.lotus-notes', 'nsf'), ('application/vnd.lotus-organizer', 'org'), ('application/vnd.lotus-screencam', 'scm'), ('application/vnd.lotus-wordpro', 'lwp'), ('application/vnd.macports.portpkg', 'portpkg'), ('application/vnd.mcd', 'mcd'), ('application/vnd.medcalcdata', 'mc1'), ('application/vnd.mediastation.cdkey', 'cdkey'), ('application/vnd.mfer', 'mwf'), ('application/vnd.mfmp', 'mfm'), ('application/vnd.micrografx.flo', 'flo'), ('application/vnd.micrografx.igx', 'igx'), ('application/vnd.mif', 'mif'), ('application/vnd.mobius.daf', 'daf');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/vnd.mobius.dis', 'dis'), ('application/vnd.mobius.mbk', 'mbk'), ('application/vnd.mobius.mqy', 'mqy'), ('application/vnd.mobius.msl', 'msl'), ('application/vnd.mobius.plc', 'plc'), ('application/vnd.mobius.txf', 'txf'), ('application/vnd.mophun.application', 'mpn'), ('application/vnd.mophun.certificate', 'mpc'), ('application/vnd.mozilla.xul+xml', 'xul'), ('application/vnd.ms-artgalry', 'cil'), ('application/vnd.ms-cab-compressed', 'cab'), ('application/vnd.ms-excel', 'xls'), ('application/vnd.ms-excel', 'xlm'), ('application/vnd.ms-excel', 'xla'), ('application/vnd.ms-excel', 'xlc'), ('application/vnd.ms-excel', 'xlt'), ('application/vnd.ms-excel', 'xlw'), ('application/vnd.ms-excel.addin.macroenabled.12', 'xlam'), ('application/vnd.ms-excel.sheet.binary.macroenabled.12', 'xlsb');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/vnd.ms-excel.sheet.macroenabled.12', 'xlsm'), ('application/vnd.ms-excel.template.macroenabled.12', 'xltm'), ('application/vnd.ms-fontobject', 'eot'), ('application/vnd.ms-htmlhelp', 'chm'), ('application/vnd.ms-ims', 'ims'), ('application/vnd.ms-lrm', 'lrm'), ('application/vnd.ms-officetheme', 'thmx'), ('application/vnd.ms-pki.seccat', 'cat'), ('application/vnd.ms-pki.stl', 'stl'), ('application/vnd.ms-powerpoint', 'ppt'), ('application/vnd.ms-powerpoint', 'pps'), ('application/vnd.ms-powerpoint', 'pot'), ('application/vnd.ms-powerpoint.addin.macroenabled.12', 'ppam'), ('application/vnd.ms-powerpoint.presentation.macroenabled.12', 'pptm'), ('application/vnd.ms-powerpoint.slide.macroenabled.12', 'sldm'), ('application/vnd.ms-powerpoint.slideshow.macroenabled.12', 'ppsm'), ('application/vnd.ms-powerpoint.template.macroenabled.12', 'potm'), ('application/vnd.ms-project', 'mpp'), ('application/vnd.ms-project', 'mpt');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/vnd.ms-word.document.macroenabled.12', 'docm'), ('application/vnd.ms-word.template.macroenabled.12', 'dotm'), ('application/vnd.ms-works', 'wps'), ('application/vnd.ms-works', 'wks'), ('application/vnd.ms-works', 'wcm'), ('application/vnd.ms-works', 'wdb'), ('application/vnd.ms-wpl', 'wpl'), ('application/vnd.ms-xpsdocument', 'xps'), ('application/vnd.mseq', 'mseq'), ('application/vnd.musician', 'mus'), ('application/vnd.muvee.style', 'msty'), ('application/vnd.mynfc', 'taglet'), ('application/vnd.neurolanguage.nlu', 'nlu'), ('application/vnd.nitf', 'ntf'), ('application/vnd.nitf', 'nitf'), ('application/vnd.noblenet-directory', 'nnd'), ('application/vnd.noblenet-sealer', 'nns'), ('application/vnd.noblenet-web', 'nnw'), ('application/vnd.nokia.n-gage.data', 'ngdat');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/vnd.nokia.n-gage.symbian.install', 'n-gage'), ('application/vnd.nokia.radio-preset', 'rpst'), ('application/vnd.nokia.radio-presets', 'rpss'), ('application/vnd.novadigm.edm', 'edm'), ('application/vnd.novadigm.edx', 'edx'), ('application/vnd.novadigm.ext', 'ext'), ('application/vnd.oasis.opendocument.chart', 'odc'), ('application/vnd.oasis.opendocument.chart-template', 'otc'), ('application/vnd.oasis.opendocument.database', 'odb'), ('application/vnd.oasis.opendocument.formula', 'odf'), ('application/vnd.oasis.opendocument.formula-template', 'odft'), ('application/vnd.oasis.opendocument.graphics', 'odg'), ('application/vnd.oasis.opendocument.graphics-template', 'otg'), ('application/vnd.oasis.opendocument.image', 'odi'), ('application/vnd.oasis.opendocument.image-template', 'oti'), ('application/vnd.oasis.opendocument.presentation', 'odp'), ('application/vnd.oasis.opendocument.presentation-template', 'otp'), ('application/vnd.oasis.opendocument.spreadsheet', 'ods'), ('application/vnd.oasis.opendocument.spreadsheet-template', 'ots');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/vnd.oasis.opendocument.text', 'odt'), ('application/vnd.oasis.opendocument.text-master', 'odm'), ('application/vnd.oasis.opendocument.text-template', 'ott'), ('application/vnd.oasis.opendocument.text-web', 'oth'), ('application/vnd.olpc-sugar', 'xo'), ('application/vnd.oma.dd2+xml', 'dd2'), ('application/vnd.openofficeorg.extension', 'oxt'), ('application/vnd.openxmlformats-officedocument.presentationml.presentation', 'pptx'), ('application/vnd.openxmlformats-officedocument.presentationml.slide', 'sldx'), ('application/vnd.openxmlformats-officedocument.presentationml.slideshow', 'ppsx'), ('application/vnd.openxmlformats-officedocument.presentationml.template', 'potx'), ('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'xlsx'), ('application/vnd.openxmlformats-officedocument.spreadsheetml.template', 'xltx'), ('application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'docx'), ('application/vnd.openxmlformats-officedocument.wordprocessingml.template', 'dotx'), ('application/vnd.osgeo.mapguide.package', 'mgp'), ('application/vnd.osgi.dp', 'dp'), ('application/vnd.osgi.subsystem', 'esa'), ('application/vnd.palm', 'pdb');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/vnd.palm', 'pqa'), ('application/vnd.palm', 'oprc'), ('application/vnd.pawaafile', 'paw'), ('application/vnd.pg.format', 'str'), ('application/vnd.pg.osasli', 'ei6'), ('application/vnd.picsel', 'efif'), ('application/vnd.pmi.widget', 'wg'), ('application/vnd.pocketlearn', 'plf'), ('application/vnd.powerbuilder6', 'pbd'), ('application/vnd.previewsystems.box', 'box'), ('application/vnd.proteus.magazine', 'mgz'), ('application/vnd.publishare-delta-tree', 'qps'), ('application/vnd.pvi.ptid1', 'ptid'), ('application/vnd.quark.quarkxpress', 'qxd'), ('application/vnd.quark.quarkxpress', 'qxt'), ('application/vnd.quark.quarkxpress', 'qwd'), ('application/vnd.quark.quarkxpress', 'qwt'), ('application/vnd.quark.quarkxpress', 'qxl'), ('application/vnd.quark.quarkxpress', 'qxb');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/vnd.realvnc.bed', 'bed'), ('application/vnd.recordare.musicxml', 'mxl'), ('application/vnd.recordare.musicxml+xml', 'musicxml'), ('application/vnd.rig.cryptonote', 'cryptonote'), ('application/vnd.rim.cod', 'cod'), ('application/vnd.rn-realmedia', 'rm'), ('application/vnd.rn-realmedia-vbr', 'rmvb'), ('application/vnd.route66.link66+xml', 'link66'), ('application/vnd.sailingtracker.track', 'st'), ('application/vnd.seemail', 'see'), ('application/vnd.sema', 'sema'), ('application/vnd.semd', 'semd'), ('application/vnd.semf', 'semf'), ('application/vnd.shana.informed.formdata', 'ifm'), ('application/vnd.shana.informed.formtemplate', 'itp'), ('application/vnd.shana.informed.interchange', 'iif'), ('application/vnd.shana.informed.package', 'ipk'), ('application/vnd.simtech-mindmapper', 'twd'), ('application/vnd.simtech-mindmapper', 'twds');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/vnd.smaf', 'mmf'), ('application/vnd.smart.teacher', 'teacher'), ('application/vnd.solent.sdkm+xml', 'sdkm'), ('application/vnd.solent.sdkm+xml', 'sdkd'), ('application/vnd.spotfire.dxp', 'dxp'), ('application/vnd.spotfire.sfs', 'sfs'), ('application/vnd.stardivision.calc', 'sdc'), ('application/vnd.stardivision.draw', 'sda'), ('application/vnd.stardivision.impress', 'sdd'), ('application/vnd.stardivision.math', 'smf'), ('application/vnd.stardivision.writer', 'sdw'), ('application/vnd.stardivision.writer', 'vor'), ('application/vnd.stardivision.writer-global', 'sgl'), ('application/vnd.stepmania.package', 'smzip'), ('application/vnd.stepmania.stepchart', 'sm'), ('application/vnd.sun.xml.calc', 'sxc'), ('application/vnd.sun.xml.calc.template', 'stc'), ('application/vnd.sun.xml.draw', 'sxd'), ('application/vnd.sun.xml.draw.template', 'std');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/vnd.sun.xml.impress', 'sxi'), ('application/vnd.sun.xml.impress.template', 'sti'), ('application/vnd.sun.xml.math', 'sxm'), ('application/vnd.sun.xml.writer', 'sxw'), ('application/vnd.sun.xml.writer.global', 'sxg'), ('application/vnd.sun.xml.writer.template', 'stw'), ('application/vnd.sus-calendar', 'sus'), ('application/vnd.sus-calendar', 'susp'), ('application/vnd.svd', 'svd'), ('application/vnd.symbian.install', 'sis'), ('application/vnd.symbian.install', 'sisx'), ('application/vnd.syncml+xml', 'xsm'), ('application/vnd.syncml.dm+wbxml', 'bdm'), ('application/vnd.syncml.dm+xml', 'xdm'), ('application/vnd.tao.intent-module-archive', 'tao'), ('application/vnd.tcpdump.pcap', 'pcap'), ('application/vnd.tcpdump.pcap', 'cap'), ('application/vnd.tcpdump.pcap', 'dmp'), ('application/vnd.tmobile-livetv', 'tmo');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/vnd.trid.tpt', 'tpt'), ('application/vnd.triscape.mxs', 'mxs'), ('application/vnd.trueapp', 'tra'), ('application/vnd.ufdl', 'ufd'), ('application/vnd.ufdl', 'ufdl'), ('application/vnd.uiq.theme', 'utz'), ('application/vnd.umajin', 'umj'), ('application/vnd.unity', 'unityweb'), ('application/vnd.uoml+xml', 'uoml'), ('application/vnd.vcx', 'vcx'), ('application/vnd.visio', 'vsd'), ('application/vnd.visio', 'vst'), ('application/vnd.visio', 'vss'), ('application/vnd.visio', 'vsw'), ('application/vnd.visionary', 'vis'), ('application/vnd.vsf', 'vsf'), ('application/vnd.wap.wbxml', 'wbxml'), ('application/vnd.wap.wmlc', 'wmlc'), ('application/vnd.wap.wmlscriptc', 'wmlsc');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/vnd.webturbo', 'wtb'), ('application/vnd.wolfram.player', 'nbp'), ('application/vnd.wordperfect', 'wpd'), ('application/vnd.wqd', 'wqd'), ('application/vnd.wt.stf', 'stf'), ('application/vnd.xara', 'xar'), ('application/vnd.xfdl', 'xfdl'), ('application/vnd.yamaha.hv-dic', 'hvd'), ('application/vnd.yamaha.hv-script', 'hvs'), ('application/vnd.yamaha.hv-voice', 'hvp'), ('application/vnd.yamaha.openscoreformat', 'osf'), ('application/vnd.yamaha.openscoreformat.osfpvg+xml', 'osfpvg'), ('application/vnd.yamaha.smaf-audio', 'saf'), ('application/vnd.yamaha.smaf-phrase', 'spf'), ('application/vnd.yellowriver-custom-menu', 'cmp'), ('application/vnd.zul', 'zir'), ('application/vnd.zul', 'zirz'), ('application/vnd.zzazz.deck+xml', 'zaz'), ('application/voicexml+xml', 'vxml');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/widget', 'wgt'), ('application/winhlp', 'hlp'), ('application/wsdl+xml', 'wsdl'), ('application/wspolicy+xml', 'wspolicy'), ('application/x-7z-compressed', '7z'), ('application/x-abiword', 'abw'), ('application/x-ace-compressed', 'ace'), ('application/x-apple-diskimage', 'dmg'), ('application/x-authorware-bin', 'aab'), ('application/x-authorware-bin', 'x32'), ('application/x-authorware-bin', 'u32'), ('application/x-authorware-bin', 'vox'), ('application/x-authorware-map', 'aam'), ('application/x-authorware-seg', 'aas'), ('application/x-bcpio', 'bcpio'), ('application/x-bittorrent', 'torrent'), ('application/x-blorb', 'blb'), ('application/x-blorb', 'blorb'), ('application/x-bzip', 'bz');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/x-bzip2', 'bz2'), ('application/x-bzip2', 'boz'), ('application/x-cbr', 'cbr'), ('application/x-cbr', 'cba'), ('application/x-cbr', 'cbt'), ('application/x-cbr', 'cbz'), ('application/x-cbr', 'cb7'), ('application/x-cdlink', 'vcd'), ('application/x-cfs-compressed', 'cfs'), ('application/x-chat', 'chat'), ('application/x-chess-pgn', 'pgn'), ('application/x-conference', 'nsc'), ('application/x-cpio', 'cpio'), ('application/x-csh', 'csh'), ('application/x-debian-package', 'deb'), ('application/x-debian-package', 'udeb'), ('application/x-dgc-compressed', 'dgc'), ('application/x-director', 'dir'), ('application/x-director', 'dcr');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/x-director', 'dxr'), ('application/x-director', 'cst'), ('application/x-director', 'cct'), ('application/x-director', 'cxt'), ('application/x-director', 'w3d'), ('application/x-director', 'fgd'), ('application/x-director', 'swa'), ('application/x-doom', 'wad'), ('application/x-dtbncx+xml', 'ncx'), ('application/x-dtbook+xml', 'dtb'), ('application/x-dtbresource+xml', 'res'), ('application/x-dvi', 'dvi'), ('application/x-envoy', 'evy'), ('application/x-eva', 'eva'), ('application/x-font-bdf', 'bdf'), ('application/x-font-ghostscript', 'gsf'), ('application/x-font-linux-psf', 'psf'), ('application/x-font-otf', 'otf'), ('application/x-font-pcf', 'pcf');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/x-font-snf', 'snf'), ('application/x-font-ttf', 'ttf'), ('application/x-font-ttf', 'ttc'), ('application/x-font-type1', 'pfa'), ('application/x-font-type1', 'pfb'), ('application/x-font-type1', 'pfm'), ('application/x-font-type1', 'afm'), ('application/font-woff', 'woff'), ('application/x-freearc', 'arc'), ('application/x-futuresplash', 'spl'), ('application/x-gca-compressed', 'gca'), ('application/x-glulx', 'ulx'), ('application/x-gnumeric', 'gnumeric'), ('application/x-gramps-xml', 'gramps'), ('application/x-gtar', 'gtar'), ('application/x-hdf', 'hdf'), ('application/x-install-instructions', 'install'), ('application/x-iso9660-image', 'iso'), ('application/x-java-jnlp-file', 'jnlp');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/x-latex', 'latex'), ('application/x-lzh-compressed', 'lzh'), ('application/x-lzh-compressed', 'lha'), ('application/x-mie', 'mie'), ('application/x-mobipocket-ebook', 'prc'), ('application/x-mobipocket-ebook', 'mobi'), ('application/x-ms-application', 'application'), ('application/x-ms-shortcut', 'lnk'), ('application/x-ms-wmd', 'wmd'), ('application/x-ms-wmz', 'wmz'), ('application/x-ms-xbap', 'xbap'), ('application/x-msaccess', 'mdb'), ('application/x-msbinder', 'obd'), ('application/x-mscardfile', 'crd'), ('application/x-msclip', 'clp'), ('application/x-msdownload', 'exe'), ('application/x-msdownload', 'dll'), ('application/x-msdownload', 'com'), ('application/x-msdownload', 'bat');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/x-msdownload', 'msi'), ('application/x-msmediaview', 'mvb'), ('application/x-msmediaview', 'm13'), ('application/x-msmediaview', 'm14'), ('application/x-msmetafile', 'wmf'), ('application/x-msmetafile', 'wmz'), ('application/x-msmetafile', 'emf'), ('application/x-msmetafile', 'emz'), ('application/x-msmoney', 'mny'), ('application/x-mspublisher', 'pub'), ('application/x-msschedule', 'scd'), ('application/x-msterminal', 'trm'), ('application/x-mswrite', 'wri'), ('application/x-netcdf', 'nc'), ('application/x-netcdf', 'cdf'), ('application/x-nzb', 'nzb'), ('application/x-pkcs12', 'p12'), ('application/x-pkcs12', 'pfx'), ('application/x-pkcs7-certificates', 'p7b');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/x-pkcs7-certificates', 'spc'), ('application/x-pkcs7-certreqresp', 'p7r'), ('application/x-rar-compressed', 'rar'), ('application/x-research-info-systems', 'ris'), ('application/x-sh', 'sh'), ('application/x-shar', 'shar'), ('application/x-shockwave-flash', 'swf'), ('application/x-silverlight-app', 'xap'), ('application/x-sql', 'sql'), ('application/x-stuffit', 'sit'), ('application/x-stuffitx', 'sitx'), ('application/x-subrip', 'srt'), ('application/x-sv4cpio', 'sv4cpio'), ('application/x-sv4crc', 'sv4crc'), ('application/x-t3vm-image', 't3'), ('application/x-tads', 'gam'), ('application/x-tar', 'tar'), ('application/x-tcl', 'tcl'), ('application/x-tex', 'tex');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/x-tex-tfm', 'tfm'), ('application/x-texinfo', 'texinfo'), ('application/x-texinfo', 'texi'), ('application/x-tgif', 'obj'), ('application/x-ustar', 'ustar'), ('application/x-wais-source', 'src'), ('application/x-x509-ca-cert', 'der'), ('application/x-x509-ca-cert', 'crt'), ('application/x-xfig', 'fig'), ('application/x-xliff+xml', 'xlf'), ('application/x-xpinstall', 'xpi'), ('application/x-xz', 'xz'), ('application/x-zmachine', 'z1'), ('application/x-zmachine', 'z2'), ('application/x-zmachine', 'z3'), ('application/x-zmachine', 'z4'), ('application/x-zmachine', 'z5'), ('application/x-zmachine', 'z6'), ('application/x-zmachine', 'z7');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/x-zmachine', 'z8'), ('application/xaml+xml', 'xaml'), ('application/xcap-diff+xml', 'xdf'), ('application/xenc+xml', 'xenc'), ('application/xhtml+xml', 'xhtml'), ('application/xhtml+xml', 'xht'), ('application/xml', 'xml'), ('application/xml', 'xsl'), ('application/xml-dtd', 'dtd'), ('application/xop+xml', 'xop'), ('application/xproc+xml', 'xpl'), ('application/xslt+xml', 'xslt'), ('application/xspf+xml', 'xspf'), ('application/xv+xml', 'mxml'), ('application/xv+xml', 'xhvml'), ('application/xv+xml', 'xvml'), ('application/xv+xml', 'xvm'), ('application/yang', 'yang'), ('application/yin+xml', 'yin');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('application/zip', 'zip'), ('audio/adpcm', 'adp'), ('audio/basic', 'au'), ('audio/basic', 'snd'), ('audio/midi', 'mid'), ('audio/midi', 'midi'), ('audio/midi', 'kar'), ('audio/midi', 'rmi'), ('audio/mp4', 'mp4a'), ('audio/mpeg', 'mpga'), ('audio/mpeg', 'mp2'), ('audio/mpeg', 'mp2a'), ('audio/mpeg', 'mp3'), ('audio/mpeg', 'm2a'), ('audio/mpeg', 'm3a'), ('audio/ogg', 'oga'), ('audio/ogg', 'ogg'), ('audio/ogg', 'spx'), ('audio/s3m', 's3m');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('audio/silk', 'sil'), ('audio/vnd.dece.audio', 'uva'), ('audio/vnd.dece.audio', 'uvva'), ('audio/vnd.digital-winds', 'eol'), ('audio/vnd.dra', 'dra'), ('audio/vnd.dts', 'dts'), ('audio/vnd.dts.hd', 'dtshd'), ('audio/vnd.lucent.voice', 'lvp'), ('audio/vnd.ms-playready.media.pya', 'pya'), ('audio/vnd.nuera.ecelp4800', 'ecelp4800'), ('audio/vnd.nuera.ecelp7470', 'ecelp7470'), ('audio/vnd.nuera.ecelp9600', 'ecelp9600'), ('audio/vnd.rip', 'rip'), ('audio/webm', 'weba'), ('audio/x-aac', 'aac'), ('audio/x-aiff', 'aif'), ('audio/x-aiff', 'aiff'), ('audio/x-aiff', 'aifc'), ('audio/x-caf', 'caf');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('audio/x-flac', 'flac'), ('audio/x-matroska', 'mka'), ('audio/x-mpegurl', 'm3u'), ('audio/x-ms-wax', 'wax'), ('audio/x-ms-wma', 'wma'), ('audio/x-pn-realaudio', 'ram'), ('audio/x-pn-realaudio', 'ra'), ('audio/x-pn-realaudio-plugin', 'rmp'), ('audio/x-wav', 'wav'), ('audio/xm', 'xm'), ('chemical/x-cdx', 'cdx'), ('chemical/x-cif', 'cif'), ('chemical/x-cmdf', 'cmdf'), ('chemical/x-cml', 'cml'), ('chemical/x-csml', 'csml'), ('chemical/x-xyz', 'xyz'), ('image/bmp', 'bmp'), ('image/cgm', 'cgm'), ('image/g3fax', 'g3');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('image/gif', 'gif'), ('image/ief', 'ief'), ('image/jpeg', 'jpeg'), ('image/jpeg', 'jpg'), ('image/jpeg', 'jpe'), ('image/ktx', 'ktx'), ('image/png', 'png'), ('image/prs.btif', 'btif'), ('image/sgi', 'sgi'), ('image/svg+xml', 'svg'), ('image/svg+xml', 'svgz'), ('image/tiff', 'tiff'), ('image/tiff', 'tif'), ('image/vnd.adobe.photoshop', 'psd'), ('image/vnd.dece.graphic', 'uvi'), ('image/vnd.dece.graphic', 'uvvi'), ('image/vnd.dece.graphic', 'uvg'), ('image/vnd.dece.graphic', 'uvvg'), ('image/vnd.dvb.subtitle', 'sub');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('image/vnd.djvu', 'djvu'), ('image/vnd.djvu', 'djv'), ('image/vnd.dwg', 'dwg'), ('image/vnd.dxf', 'dxf'), ('image/vnd.fastbidsheet', 'fbs'), ('image/vnd.fpx', 'fpx'), ('image/vnd.fst', 'fst'), ('image/vnd.fujixerox.edmics-mmr', 'mmr'), ('image/vnd.fujixerox.edmics-rlc', 'rlc'), ('image/vnd.ms-modi', 'mdi'), ('image/vnd.ms-photo', 'wdp'), ('image/vnd.net-fpx', 'npx'), ('image/vnd.wap.wbmp', 'wbmp'), ('image/vnd.xiff', 'xif'), ('image/webp', 'webp'), ('image/x-3ds', '3ds'), ('image/x-cmu-raster', 'ras'), ('image/x-cmx', 'cmx'), ('image/x-freehand', 'fh');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('image/x-freehand', 'fhc'), ('image/x-freehand', 'fh4'), ('image/x-freehand', 'fh5'), ('image/x-freehand', 'fh7'), ('image/x-icon', 'ico'), ('image/x-mrsid-image', 'sid'), ('image/x-pcx', 'pcx'), ('image/x-pict', 'pic'), ('image/x-pict', 'pct'), ('image/x-portable-anymap', 'pnm'), ('image/x-portable-bitmap', 'pbm'), ('image/x-portable-graymap', 'pgm'), ('image/x-portable-pixmap', 'ppm'), ('image/x-rgb', 'rgb'), ('image/x-tga', 'tga'), ('image/x-xbitmap', 'xbm'), ('image/x-xpixmap', 'xpm'), ('image/x-xwindowdump', 'xwd'), ('message/rfc822', 'eml');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('message/rfc822', 'mime'), ('model/iges', 'igs'), ('model/iges', 'iges'), ('model/mesh', 'msh'), ('model/mesh', 'mesh'), ('model/mesh', 'silo'), ('model/vnd.collada+xml', 'dae'), ('model/vnd.dwf', 'dwf'), ('model/vnd.gdl', 'gdl'), ('model/vnd.gtw', 'gtw'), ('model/vnd.mts', 'mts'), ('model/vnd.vtu', 'vtu'), ('model/vrml', 'wrl'), ('model/vrml', 'vrml'), ('model/x3d+binary', 'x3db'), ('model/x3d+binary', 'x3dbz'), ('model/x3d+vrml', 'x3dv'), ('model/x3d+vrml', 'x3dvz'), ('model/x3d+xml', 'x3d');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('model/x3d+xml', 'x3dz'), ('text/cache-manifest', 'appcache'), ('text/calendar', 'ics'), ('text/calendar', 'ifb'), ('text/css', 'css'), ('text/csv', 'csv'), ('text/html', 'html'), ('text/html', 'htm'), ('text/n3', 'n3'), ('text/plain', 'txt'), ('text/plain', 'text'), ('text/plain', 'conf'), ('text/plain', 'def'), ('text/plain', 'list'), ('text/plain', 'log'), ('text/plain', 'in'), ('text/prs.lines.tag', 'dsc'), ('text/richtext', 'rtx'), ('text/sgml', 'sgml');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('text/sgml', 'sgm'), ('text/tab-separated-values', 'tsv'), ('text/troff', 't'), ('text/troff', 'tr'), ('text/troff', 'roff'), ('text/troff', 'man'), ('text/troff', 'me'), ('text/troff', 'ms'), ('text/turtle', 'ttl'), ('text/uri-list', 'uri'), ('text/uri-list', 'uris'), ('text/uri-list', 'urls'), ('text/vcard', 'vcard'), ('text/vnd.curl', 'curl'), ('text/vnd.curl.dcurl', 'dcurl'), ('text/vnd.curl.scurl', 'scurl'), ('text/vnd.curl.mcurl', 'mcurl'), ('text/vnd.dvb.subtitle', 'sub'), ('text/vnd.fly', 'fly');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('text/vnd.fmi.flexstor', 'flx'), ('text/vnd.graphviz', 'gv'), ('text/vnd.in3d.3dml', '3dml'), ('text/vnd.in3d.spot', 'spot'), ('text/vnd.sun.j2me.app-descriptor', 'jad'), ('text/vnd.wap.wml', 'wml'), ('text/vnd.wap.wmlscript', 'wmls'), ('text/x-asm', 's'), ('text/x-asm', 'asm'), ('text/x-c', 'c'), ('text/x-c', 'cc'), ('text/x-c', 'cxx'), ('text/x-c', 'cpp'), ('text/x-c', 'h'), ('text/x-c', 'hh'), ('text/x-c', 'dic'), ('text/x-fortran', 'f'), ('text/x-fortran', 'for'), ('text/x-fortran', 'f77');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('text/x-fortran', 'f90'), ('text/x-java-source', 'java'), ('text/x-opml', 'opml'), ('text/x-pascal', 'p'), ('text/x-pascal', 'pas'), ('text/x-nfo', 'nfo'), ('text/x-setext', 'etx'), ('text/x-sfv', 'sfv'), ('text/x-uuencode', 'uu'), ('text/x-vcalendar', 'vcs'), ('text/x-vcard', 'vcf'), ('video/3gpp', '3gp'), ('video/3gpp2', '3g2'), ('video/h261', 'h261'), ('video/h263', 'h263'), ('video/h264', 'h264'), ('video/jpeg', 'jpgv'), ('video/jpm', 'jpm'), ('video/jpm', 'jpgm');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('video/mj2', 'mj2'), ('video/mj2', 'mjp2'), ('video/mp4', 'mp4'), ('video/mp4', 'mp4v'), ('video/mp4', 'mpg4'), ('video/mpeg', 'mpeg'), ('video/mpeg', 'mpg'), ('video/mpeg', 'mpe'), ('video/mpeg', 'm1v'), ('video/mpeg', 'm2v'), ('video/ogg', 'ogv'), ('video/quicktime', 'qt'), ('video/quicktime', 'mov'), ('video/vnd.dece.hd', 'uvh'), ('video/vnd.dece.hd', 'uvvh'), ('video/vnd.dece.mobile', 'uvm'), ('video/vnd.dece.mobile', 'uvvm'), ('video/vnd.dece.pd', 'uvp'), ('video/vnd.dece.pd', 'uvvp');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('video/vnd.dece.sd', 'uvs'), ('video/vnd.dece.sd', 'uvvs'), ('video/vnd.dece.video', 'uvv'), ('video/vnd.dece.video', 'uvvv'), ('video/vnd.dvb.file', 'dvb'), ('video/vnd.fvt', 'fvt'), ('video/vnd.mpegurl', 'mxu'), ('video/vnd.mpegurl', 'm4u'), ('video/vnd.ms-playready.media.pyv', 'pyv'), ('video/vnd.uvvu.mp4', 'uvu'), ('video/vnd.uvvu.mp4', 'uvvu'), ('video/vnd.vivo', 'viv'), ('video/webm', 'webm'), ('video/x-f4v', 'f4v'), ('video/x-fli', 'fli'), ('video/x-flv', 'flv'), ('video/x-m4v', 'm4v'), ('video/x-matroska', 'mkv'), ('video/x-matroska', 'mk3d');
REPLACE INTO `#__files_mimetypes` (mimetype, extension) VALUES ('video/x-matroska', 'mks'), ('video/x-mng', 'mng'), ('video/x-ms-asf', 'asf'), ('video/x-ms-asf', 'asx'), ('video/x-ms-vob', 'vob'), ('video/x-ms-wm', 'wm'), ('video/x-ms-wmv', 'wmv'), ('video/x-ms-wmx', 'wmx'), ('video/x-ms-wvx', 'wvx'), ('video/x-msvideo', 'avi'), ('video/x-sgi-movie', 'movie'), ('video/x-smv', 'smv'), ('x-conference/x-cooltalk', 'ice'), ('application/x-gzip', 'gz');
