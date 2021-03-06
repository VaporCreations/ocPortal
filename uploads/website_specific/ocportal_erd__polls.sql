		CREATE TABLE ocp6_poll
		(
			id integer auto_increment NULL,
			question integer NOT NULL,
			option1 integer NOT NULL,
			option2 integer NOT NULL,
			option3 integer NOT NULL,
			option4 integer NOT NULL,
			option5 integer NOT NULL,
			option6 integer NOT NULL,
			option7 integer NOT NULL,
			option8 integer NOT NULL,
			option9 integer NOT NULL,
			option10 integer NOT NULL,
			votes1 integer NOT NULL,
			votes2 integer NOT NULL,
			votes3 integer NOT NULL,
			votes4 integer NOT NULL,
			votes5 integer NOT NULL,
			votes6 integer NOT NULL,
			votes7 integer NOT NULL,
			votes8 integer NOT NULL,
			votes9 integer NOT NULL,
			votes10 integer NOT NULL,
			allow_rating tinyint(1) NOT NULL,
			allow_comments tinyint NOT NULL,
			allow_trackbacks tinyint(1) NOT NULL,
			notes longtext NOT NULL,
			num_options tinyint NOT NULL,
			is_current tinyint(1) NOT NULL,
			date_and_time integer unsigned NOT NULL,
			submitter integer NOT NULL,
			add_time integer NOT NULL,
			poll_views integer NOT NULL,
			edit_date integer unsigned NOT NULL,
			PRIMARY KEY (id)
		) TYPE=InnoDB;

		CREATE TABLE ocp6_poll_votes
		(
			id integer auto_increment NULL,
			v_poll_id integer NOT NULL,
			v_voter_id integer NOT NULL,
			v_voter_ip varchar(40) NOT NULL,
			v_vote_for tinyint NOT NULL,
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


		CREATE INDEX `poll.question` ON ocp6_poll(question);
		ALTER TABLE ocp6_poll ADD FOREIGN KEY `poll.question` (question) REFERENCES ocp6_translate (id);

		CREATE INDEX `poll.option1` ON ocp6_poll(option1);
		ALTER TABLE ocp6_poll ADD FOREIGN KEY `poll.option1` (option1) REFERENCES ocp6_translate (id);

		CREATE INDEX `poll.option2` ON ocp6_poll(option2);
		ALTER TABLE ocp6_poll ADD FOREIGN KEY `poll.option2` (option2) REFERENCES ocp6_translate (id);

		CREATE INDEX `poll.option3` ON ocp6_poll(option3);
		ALTER TABLE ocp6_poll ADD FOREIGN KEY `poll.option3` (option3) REFERENCES ocp6_translate (id);

		CREATE INDEX `poll.option4` ON ocp6_poll(option4);
		ALTER TABLE ocp6_poll ADD FOREIGN KEY `poll.option4` (option4) REFERENCES ocp6_translate (id);

		CREATE INDEX `poll.option5` ON ocp6_poll(option5);
		ALTER TABLE ocp6_poll ADD FOREIGN KEY `poll.option5` (option5) REFERENCES ocp6_translate (id);

		CREATE INDEX `poll.option6` ON ocp6_poll(option6);
		ALTER TABLE ocp6_poll ADD FOREIGN KEY `poll.option6` (option6) REFERENCES ocp6_translate (id);

		CREATE INDEX `poll.option7` ON ocp6_poll(option7);
		ALTER TABLE ocp6_poll ADD FOREIGN KEY `poll.option7` (option7) REFERENCES ocp6_translate (id);

		CREATE INDEX `poll.option8` ON ocp6_poll(option8);
		ALTER TABLE ocp6_poll ADD FOREIGN KEY `poll.option8` (option8) REFERENCES ocp6_translate (id);

		CREATE INDEX `poll.option9` ON ocp6_poll(option9);
		ALTER TABLE ocp6_poll ADD FOREIGN KEY `poll.option9` (option9) REFERENCES ocp6_translate (id);

		CREATE INDEX `poll.option10` ON ocp6_poll(option10);
		ALTER TABLE ocp6_poll ADD FOREIGN KEY `poll.option10` (option10) REFERENCES ocp6_translate (id);

		CREATE INDEX `poll.submitter` ON ocp6_poll(submitter);
		ALTER TABLE ocp6_poll ADD FOREIGN KEY `poll.submitter` (submitter) REFERENCES ocp6_f_members (id);

		CREATE INDEX `poll_votes.v_poll_id` ON ocp6_poll_votes(v_poll_id);
		ALTER TABLE ocp6_poll_votes ADD FOREIGN KEY `poll_votes.v_poll_id` (v_poll_id) REFERENCES ocp6_poll (poll_id);

		CREATE INDEX `poll_votes.v_voter_id` ON ocp6_poll_votes(v_voter_id);
		ALTER TABLE ocp6_poll_votes ADD FOREIGN KEY `poll_votes.v_voter_id` (v_voter_id) REFERENCES ocp6_f_members (id);

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
