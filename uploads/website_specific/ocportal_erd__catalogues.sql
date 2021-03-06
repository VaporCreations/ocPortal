		CREATE TABLE ocp6_catalogues
		(
			c_display_type tinyint NOT NULL,
			c_is_tree tinyint(1) NOT NULL,
			c_notes longtext NOT NULL,
			c_add_date integer unsigned NOT NULL,
			c_submit_points integer NOT NULL,
			c_ecommerce tinyint(1) NOT NULL,
			c_send_view_reports varchar(80) NOT NULL,
			c_name varchar(80) NULL,
			c_title integer NOT NULL,
			c_description integer NOT NULL,
			PRIMARY KEY (c_name)
		) TYPE=InnoDB;

		CREATE TABLE ocp6_catalogue_categories
		(
			cc_parent_id integer NOT NULL,
			cc_move_days_higher integer NOT NULL,
			cc_move_days_lower integer NOT NULL,
			cc_move_target integer NOT NULL,
			cc_description integer NOT NULL,
			rep_image varchar(255) NOT NULL,
			cc_notes longtext NOT NULL,
			cc_add_date integer unsigned NOT NULL,
			id integer auto_increment NULL,
			c_name varchar(80) NOT NULL,
			cc_title integer NOT NULL,
			PRIMARY KEY (id)
		) TYPE=InnoDB;

		CREATE TABLE ocp6_catalogue_fields
		(
			c_name varchar(80) NOT NULL,
			cf_name integer NOT NULL,
			cf_description integer NOT NULL,
			cf_type varchar(80) NOT NULL,
			cf_order integer NOT NULL,
			cf_defines_order tinyint NOT NULL,
			cf_visible tinyint(1) NOT NULL,
			cf_searchable tinyint(1) NOT NULL,
			cf_default longtext NOT NULL,
			cf_required tinyint(1) NOT NULL,
			cf_put_in_category tinyint(1) NOT NULL,
			cf_put_in_search tinyint(1) NOT NULL,
			id integer auto_increment NULL,
			PRIMARY KEY (id)
		) TYPE=InnoDB;

		CREATE TABLE ocp6_catalogue_entries
		(
			allow_comments tinyint NOT NULL,
			ce_views_prior integer NOT NULL,
			allow_trackbacks tinyint(1) NOT NULL,
			allow_rating tinyint(1) NOT NULL,
			ce_validated tinyint(1) NOT NULL,
			notes longtext NOT NULL,
			ce_add_date integer unsigned NOT NULL,
			ce_edit_date integer unsigned NOT NULL,
			ce_views integer NOT NULL,
			id integer auto_increment NULL,
			c_name varchar(80) NOT NULL,
			cc_id integer NOT NULL,
			ce_submitter integer NOT NULL,
			ce_last_moved integer NOT NULL,
			PRIMARY KEY (id)
		) TYPE=InnoDB;

		CREATE TABLE ocp6_catalogue_efv_long_trans
		(
			id integer auto_increment NULL,
			cf_id integer NOT NULL,
			ce_id integer NOT NULL,
			cv_value integer NOT NULL,
			PRIMARY KEY (id)
		) TYPE=InnoDB;

		CREATE TABLE ocp6_catalogue_efv_long
		(
			id integer auto_increment NULL,
			cf_id integer NOT NULL,
			ce_id integer NOT NULL,
			cv_value longtext NOT NULL,
			PRIMARY KEY (id)
		) TYPE=InnoDB;

		CREATE TABLE ocp6_catalogue_efv_short_trans
		(
			id integer auto_increment NULL,
			cf_id integer NOT NULL,
			ce_id integer NOT NULL,
			cv_value integer NOT NULL,
			PRIMARY KEY (id)
		) TYPE=InnoDB;

		CREATE TABLE ocp6_catalogue_efv_short
		(
			id integer auto_increment NULL,
			cf_id integer NOT NULL,
			ce_id integer NOT NULL,
			cv_value varchar(255) NOT NULL,
			PRIMARY KEY (id)
		) TYPE=InnoDB;

		CREATE TABLE ocp6_translate
		(
			id integer auto_increment NULL,
			language varchar(5) NULL,
			importance_level tinyint NOT NULL,
			text_original longtext NOT NULL,
			text_parsed longtext NOT NULL,
			broken tinyint(1) NOT NULL,
			source_user integer NOT NULL,
			PRIMARY KEY (id,language)
		) TYPE=InnoDB;

		CREATE TABLE ocp6_f_members
		(
			id integer auto_increment NULL,
			m_username varchar(80) NOT NULL,
			m_pass_hash_salted varchar(255) NOT NULL,
			m_pass_salt varchar(255) NOT NULL,
			m_theme varchar(80) NOT NULL,
			m_avatar_url varchar(255) NOT NULL,
			m_validated tinyint(1) NOT NULL,
			m_validated_email_confirm_code varchar(255) NOT NULL,
			m_cache_num_posts integer NOT NULL,
			m_cache_warnings integer NOT NULL,
			m_join_time integer unsigned NOT NULL,
			m_timezone_offset integer NOT NULL,
			m_primary_group integer NOT NULL,
			m_last_visit_time integer unsigned NOT NULL,
			m_last_submit_time integer unsigned NOT NULL,
			m_signature integer NOT NULL,
			m_is_perm_banned tinyint(1) NOT NULL,
			m_preview_posts tinyint(1) NOT NULL,
			m_dob_day integer NOT NULL,
			m_dob_month integer NOT NULL,
			m_dob_year integer NOT NULL,
			m_reveal_age tinyint(1) NOT NULL,
			m_email_address varchar(255) NOT NULL,
			m_title varchar(255) NOT NULL,
			m_photo_url varchar(255) NOT NULL,
			m_photo_thumb_url varchar(255) NOT NULL,
			m_views_signatures tinyint(1) NOT NULL,
			m_track_contributed_topics tinyint(1) NOT NULL,
			m_language varchar(80) NOT NULL,
			m_ip_address varchar(40) NOT NULL,
			m_allow_emails tinyint(1) NOT NULL,
			m_notes longtext NOT NULL,
			m_zone_wide tinyint(1) NOT NULL,
			m_highlighted_name tinyint(1) NOT NULL,
			m_pt_allow varchar(255) NOT NULL,
			m_pt_rules_text integer NOT NULL,
			m_max_email_attach_size_mb integer NOT NULL,
			m_password_change_code varchar(255) NOT NULL,
			m_password_compat_scheme varchar(80) NOT NULL,
			m_on_probation_until integer unsigned NOT NULL,
			PRIMARY KEY (id)
		) TYPE=InnoDB;

		CREATE TABLE ocp6_f_groups
		(
			id integer auto_increment NULL,
			g_name integer NOT NULL,
			g_is_default tinyint(1) NOT NULL,
			g_is_presented_at_install tinyint(1) NOT NULL,
			g_is_super_admin tinyint(1) NOT NULL,
			g_is_super_moderator tinyint(1) NOT NULL,
			g_group_leader integer NOT NULL,
			g_title integer NOT NULL,
			g_promotion_target integer NOT NULL,
			g_promotion_threshold integer NOT NULL,
			g_flood_control_submit_secs integer NOT NULL,
			g_flood_control_access_secs integer NOT NULL,
			g_gift_points_base integer NOT NULL,
			g_gift_points_per_day integer NOT NULL,
			g_max_daily_upload_mb integer NOT NULL,
			g_max_attachments_per_post integer NOT NULL,
			g_max_avatar_width integer NOT NULL,
			g_max_avatar_height integer NOT NULL,
			g_max_post_length_comcode integer NOT NULL,
			g_max_sig_length_comcode integer NOT NULL,
			g_enquire_on_new_ips tinyint(1) NOT NULL,
			g_rank_image varchar(80) NOT NULL,
			g_hidden tinyint(1) NOT NULL,
			g_order integer NOT NULL,
			g_rank_image_pri_only tinyint(1) NOT NULL,
			g_open_membership tinyint(1) NOT NULL,
			g_is_private_club tinyint(1) NOT NULL,
			PRIMARY KEY (id)
		) TYPE=InnoDB;


		CREATE INDEX `catalogues.c_title` ON ocp6_catalogues(c_title);
		ALTER TABLE ocp6_catalogues ADD FOREIGN KEY `catalogues.c_title` (c_title) REFERENCES ocp6_translate (id);

		CREATE INDEX `catalogues.c_description` ON ocp6_catalogues(c_description);
		ALTER TABLE ocp6_catalogues ADD FOREIGN KEY `catalogues.c_description` (c_description) REFERENCES ocp6_translate (id);

		CREATE INDEX `catalogue_categories.cc_parent_id` ON ocp6_catalogue_categories(cc_parent_id);
		ALTER TABLE ocp6_catalogue_categories ADD FOREIGN KEY `catalogue_categories.cc_parent_id` (cc_parent_id) REFERENCES ocp6_catalogue_categories (id);

		CREATE INDEX `catalogue_categories.cc_move_target` ON ocp6_catalogue_categories(cc_move_target);
		ALTER TABLE ocp6_catalogue_categories ADD FOREIGN KEY `catalogue_categories.cc_move_target` (cc_move_target) REFERENCES ocp6_catalogue_categories (id);

		CREATE INDEX `catalogue_categories.cc_description` ON ocp6_catalogue_categories(cc_description);
		ALTER TABLE ocp6_catalogue_categories ADD FOREIGN KEY `catalogue_categories.cc_description` (cc_description) REFERENCES ocp6_translate (id);

		CREATE INDEX `catalogue_categories.c_name` ON ocp6_catalogue_categories(c_name);
		ALTER TABLE ocp6_catalogue_categories ADD FOREIGN KEY `catalogue_categories.c_name` (c_name) REFERENCES ocp6_catalogues (c_name);

		CREATE INDEX `catalogue_categories.cc_title` ON ocp6_catalogue_categories(cc_title);
		ALTER TABLE ocp6_catalogue_categories ADD FOREIGN KEY `catalogue_categories.cc_title` (cc_title) REFERENCES ocp6_translate (id);

		CREATE INDEX `catalogue_fields.c_name` ON ocp6_catalogue_fields(c_name);
		ALTER TABLE ocp6_catalogue_fields ADD FOREIGN KEY `catalogue_fields.c_name` (c_name) REFERENCES ocp6_catalogues (c_name);

		CREATE INDEX `catalogue_fields.cf_name` ON ocp6_catalogue_fields(cf_name);
		ALTER TABLE ocp6_catalogue_fields ADD FOREIGN KEY `catalogue_fields.cf_name` (cf_name) REFERENCES ocp6_translate (id);

		CREATE INDEX `catalogue_fields.cf_description` ON ocp6_catalogue_fields(cf_description);
		ALTER TABLE ocp6_catalogue_fields ADD FOREIGN KEY `catalogue_fields.cf_description` (cf_description) REFERENCES ocp6_translate (id);

		CREATE INDEX `catalogue_entries.c_name` ON ocp6_catalogue_entries(c_name);
		ALTER TABLE ocp6_catalogue_entries ADD FOREIGN KEY `catalogue_entries.c_name` (c_name) REFERENCES ocp6_catalogues (c_name);

		CREATE INDEX `catalogue_entries.cc_id` ON ocp6_catalogue_entries(cc_id);
		ALTER TABLE ocp6_catalogue_entries ADD FOREIGN KEY `catalogue_entries.cc_id` (cc_id) REFERENCES ocp6_catalogue_categories (id);

		CREATE INDEX `catalogue_entries.ce_submitter` ON ocp6_catalogue_entries(ce_submitter);
		ALTER TABLE ocp6_catalogue_entries ADD FOREIGN KEY `catalogue_entries.ce_submitter` (ce_submitter) REFERENCES ocp6_f_members (id);

		CREATE INDEX `catalogue_efv_long_trans.cf_id` ON ocp6_catalogue_efv_long_trans(cf_id);
		ALTER TABLE ocp6_catalogue_efv_long_trans ADD FOREIGN KEY `catalogue_efv_long_trans.cf_id` (cf_id) REFERENCES ocp6_catalogue_fields (id);

		CREATE INDEX `catalogue_efv_long_trans.ce_id` ON ocp6_catalogue_efv_long_trans(ce_id);
		ALTER TABLE ocp6_catalogue_efv_long_trans ADD FOREIGN KEY `catalogue_efv_long_trans.ce_id` (ce_id) REFERENCES ocp6_catalogue_entries (id);

		CREATE INDEX `catalogue_efv_long_trans.cv_value` ON ocp6_catalogue_efv_long_trans(cv_value);
		ALTER TABLE ocp6_catalogue_efv_long_trans ADD FOREIGN KEY `catalogue_efv_long_trans.cv_value` (cv_value) REFERENCES ocp6_translate (id);

		CREATE INDEX `catalogue_efv_long.cf_id` ON ocp6_catalogue_efv_long(cf_id);
		ALTER TABLE ocp6_catalogue_efv_long ADD FOREIGN KEY `catalogue_efv_long.cf_id` (cf_id) REFERENCES ocp6_catalogue_fields (id);

		CREATE INDEX `catalogue_efv_long.ce_id` ON ocp6_catalogue_efv_long(ce_id);
		ALTER TABLE ocp6_catalogue_efv_long ADD FOREIGN KEY `catalogue_efv_long.ce_id` (ce_id) REFERENCES ocp6_catalogue_entries (id);

		CREATE INDEX `catalogue_efv_short_trans.cf_id` ON ocp6_catalogue_efv_short_trans(cf_id);
		ALTER TABLE ocp6_catalogue_efv_short_trans ADD FOREIGN KEY `catalogue_efv_short_trans.cf_id` (cf_id) REFERENCES ocp6_catalogue_fields (id);

		CREATE INDEX `catalogue_efv_short_trans.ce_id` ON ocp6_catalogue_efv_short_trans(ce_id);
		ALTER TABLE ocp6_catalogue_efv_short_trans ADD FOREIGN KEY `catalogue_efv_short_trans.ce_id` (ce_id) REFERENCES ocp6_catalogue_entries (id);

		CREATE INDEX `catalogue_efv_short_trans.cv_value` ON ocp6_catalogue_efv_short_trans(cv_value);
		ALTER TABLE ocp6_catalogue_efv_short_trans ADD FOREIGN KEY `catalogue_efv_short_trans.cv_value` (cv_value) REFERENCES ocp6_translate (id);

		CREATE INDEX `catalogue_efv_short.cf_id` ON ocp6_catalogue_efv_short(cf_id);
		ALTER TABLE ocp6_catalogue_efv_short ADD FOREIGN KEY `catalogue_efv_short.cf_id` (cf_id) REFERENCES ocp6_catalogue_fields (id);

		CREATE INDEX `catalogue_efv_short.ce_id` ON ocp6_catalogue_efv_short(ce_id);
		ALTER TABLE ocp6_catalogue_efv_short ADD FOREIGN KEY `catalogue_efv_short.ce_id` (ce_id) REFERENCES ocp6_catalogue_entries (id);

		CREATE INDEX `translate.source_user` ON ocp6_translate(source_user);
		ALTER TABLE ocp6_translate ADD FOREIGN KEY `translate.source_user` (source_user) REFERENCES ocp6_f_members (id);

		CREATE INDEX `f_members.m_primary_group` ON ocp6_f_members(m_primary_group);
		ALTER TABLE ocp6_f_members ADD FOREIGN KEY `f_members.m_primary_group` (m_primary_group) REFERENCES ocp6_f_groups (id);

		CREATE INDEX `f_members.m_signature` ON ocp6_f_members(m_signature);
		ALTER TABLE ocp6_f_members ADD FOREIGN KEY `f_members.m_signature` (m_signature) REFERENCES ocp6_translate (id);

		CREATE INDEX `f_members.m_pt_rules_text` ON ocp6_f_members(m_pt_rules_text);
		ALTER TABLE ocp6_f_members ADD FOREIGN KEY `f_members.m_pt_rules_text` (m_pt_rules_text) REFERENCES ocp6_translate (id);

		CREATE INDEX `f_groups.g_name` ON ocp6_f_groups(g_name);
		ALTER TABLE ocp6_f_groups ADD FOREIGN KEY `f_groups.g_name` (g_name) REFERENCES ocp6_translate (id);

		CREATE INDEX `f_groups.g_group_leader` ON ocp6_f_groups(g_group_leader);
		ALTER TABLE ocp6_f_groups ADD FOREIGN KEY `f_groups.g_group_leader` (g_group_leader) REFERENCES ocp6_f_members (id);

		CREATE INDEX `f_groups.g_title` ON ocp6_f_groups(g_title);
		ALTER TABLE ocp6_f_groups ADD FOREIGN KEY `f_groups.g_title` (g_title) REFERENCES ocp6_translate (id);

		CREATE INDEX `f_groups.g_promotion_target` ON ocp6_f_groups(g_promotion_target);
		ALTER TABLE ocp6_f_groups ADD FOREIGN KEY `f_groups.g_promotion_target` (g_promotion_target) REFERENCES ocp6_f_groups (id);
