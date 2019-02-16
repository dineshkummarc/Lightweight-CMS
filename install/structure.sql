-- phpMyAdmin SQL Dump
-- version 4.7.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 28, 2017 at 02:32 PM
-- Server version: 5.7.19-log
-- PHP Version: 5.6.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------

--
-- Table structure for table `activation`
--

DROP TABLE IF EXISTS `activation`;
CREATE TABLE `activation` (
  `user_id` int(11) NOT NULL,
  `activation_key` varchar(15) CHARACTER SET latin1 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `attachments`
--

DROP TABLE IF EXISTS `attachments`;
CREATE TABLE `attachments` (
  `id` int(11) NOT NULL,
  `file_name` varchar(80) COLLATE utf8_bin NOT NULL,
  `actual_name` varchar(260) COLLATE utf8_bin NOT NULL,
  `file_extension` varchar(260) COLLATE utf8_bin NOT NULL,
  `comment` varchar(255) COLLATE utf8_bin NOT NULL,
  `user_id` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `size` int(11) NOT NULL,
  `downloads` int(11) NOT NULL,
  `is_image` tinyint(1) NOT NULL,
  `form` int(11) NOT NULL,
  `is_pm` tinyint(1) NOT NULL DEFAULT '0',
  `forum_id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `exif_info` varchar(1024) COLLATE utf8_bin NOT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `bans`
--

DROP TABLE IF EXISTS `bans`;
CREATE TABLE `bans` (
  `ban_id` int(8) NOT NULL,
  `banned_user_id` int(11) NOT NULL,
  `ip_address` varchar(15) COLLATE utf8_bin NOT NULL,
  `email` varchar(256) COLLATE utf8_bin NOT NULL,
  `start_at` int(11) NOT NULL,
  `end_at` int(11) NOT NULL,
  `reason` text COLLATE utf8_bin NOT NULL,
  `reason_to_banned` text COLLATE utf8_bin NOT NULL,
  `banned_by` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `bbcode`
--

DROP TABLE IF EXISTS `bbcode`;
CREATE TABLE `bbcode` (
  `bbcode_id` int(10) UNSIGNED NOT NULL,
  `bbcode_hint` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `bbcode` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `bbcode_html` longtext COLLATE utf8_bin,
  `bbcode_show` tinyint(1) UNSIGNED DEFAULT NULL,
  `attrib_func` varchar(20) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `drafts`
--

DROP TABLE IF EXISTS `drafts`;
CREATE TABLE `drafts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `data` longtext COLLATE utf8_bin NOT NULL,
  `post_id` int(11) NOT NULL,
  `title` varchar(128) CHARACTER SET latin1 NOT NULL,
  `by_user` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `forms`
--

DROP TABLE IF EXISTS `forms`;
CREATE TABLE `forms` (
  `id` int(15) NOT NULL,
  `user_id` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `action` varchar(100) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `forum`
--

DROP TABLE IF EXISTS `forum`;
CREATE TABLE `forum` (
  `forum_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `forum_name` varchar(260) COLLATE utf8_bin DEFAULT NULL,
  `description` text COLLATE utf8_bin NOT NULL,
  `forum_type` int(1) DEFAULT NULL,
  `comments` int(11) NOT NULL,
  `display` tinyint(1) NOT NULL,
  `display_order` int(11) NOT NULL,
  `forum_password` varchar(20) COLLATE utf8_bin NOT NULL DEFAULT '',
  `posts` int(11) NOT NULL,
  `topics` int(11) NOT NULL,
  `last_post_id` int(11) NOT NULL,
  `last_post_time` int(11) NOT NULL,
  `last_post_title` varchar(260) COLLATE utf8_bin NOT NULL,
  `last_post_poster_id` int(11) NOT NULL,
  `last_post_poster_name` varchar(255) COLLATE utf8_bin NOT NULL,
  `last_post_poster_color` varchar(7) COLLATE utf8_bin NOT NULL,
  `google_fragment` int(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `forum_session`
--

DROP TABLE IF EXISTS `forum_session`;
CREATE TABLE `forum_session` (
  `forum_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_id` varchar(15) CHARACTER SET latin1 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `general`
--

DROP TABLE IF EXISTS `general`;
CREATE TABLE `general` (
  `setting` varchar(128) COLLATE utf8_bin NOT NULL,
  `class` varchar(10) COLLATE utf8_bin NOT NULL,
  `type` varchar(20) COLLATE utf8_bin NOT NULL,
  `value` text COLLATE utf8_bin NOT NULL,
  `readonly` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (
  `id` int(10) NOT NULL,
  `type` smallint(6) DEFAULT NULL,
  `founder_manage` tinyint(1) UNSIGNED DEFAULT NULL,
  `display_on_legend` tinyint(1) UNSIGNED DEFAULT NULL,
  `rank` int(10) DEFAULT NULL,
  `color` varchar(7) COLLATE utf8_bin DEFAULT NULL,
  `description` varchar(1024) COLLATE utf8_bin DEFAULT NULL,
  `name` varchar(260) COLLATE utf8_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `group_permissions`
--

DROP TABLE IF EXISTS `group_permissions`;
CREATE TABLE `group_permissions` (
  `group_id` int(11) DEFAULT NULL,
  `forum_id` int(11) DEFAULT NULL,
  `permission_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `hashtags`
--

DROP TABLE IF EXISTS `hashtags`;
CREATE TABLE `hashtags` (
  `tag` varchar(100) COLLATE utf8_bin NOT NULL,
  `forum_id` int(11) NOT NULL,
  `hit_count` int(11) NOT NULL,
  `use_count` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `input`
--

DROP TABLE IF EXISTS `input`;
CREATE TABLE `input` (
  `id` int(11) NOT NULL,
  `name` varchar(60) CHARACTER SET latin1 NOT NULL,
  `method` char(4) CHARACTER SET latin1 NOT NULL,
  `type` char(20) CHARACTER SET latin1 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

DROP TABLE IF EXISTS `likes`;
CREATE TABLE `likes` (
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `time` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `log`
--

DROP TABLE IF EXISTS `log`;
CREATE TABLE `log` (
  `id` int(11) NOT NULL,
  `type` varchar(20) COLLATE utf8_bin NOT NULL,
  `user` varchar(100) COLLATE utf8_bin NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip` varchar(45) COLLATE utf8_bin NOT NULL,
  `action` varchar(100) COLLATE utf8_bin NOT NULL,
  `message` varchar(1000) COLLATE utf8_bin NOT NULL,
  `time` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE `login_attempts` (
  `username` varchar(255) COLLATE utf8_bin NOT NULL,
  `time` int(11) NOT NULL,
  `ip` varchar(15) COLLATE utf8_bin NOT NULL,
  `user_agent` varchar(255) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

DROP TABLE IF EXISTS `modules`;
CREATE TABLE `modules` (
  `module_id` int(11) NOT NULL,
  `module_name` varchar(128) CHARACTER SET latin1 NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `type` tinyint(1) NOT NULL,
  `class` varchar(3) CHARACTER SET latin1 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `permission_id` mediumint(8) UNSIGNED NOT NULL,
  `permission_class` varchar(60) COLLATE utf8_bin DEFAULT NULL,
  `name` varchar(100) COLLATE utf8_bin DEFAULT NULL,
  `founder` tinyint(1) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `post`
--

DROP TABLE IF EXISTS `post`;
CREATE TABLE `post` (
  `id` int(11) NOT NULL,
  `topic_id` int(11) DEFAULT NULL,
  `forum_id` int(11) DEFAULT NULL,
  `ip` varchar(30) COLLATE utf8_bin DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  `reported` int(1) DEFAULT NULL,
  `bbcode` int(1) DEFAULT NULL,
  `username` varchar(260) COLLATE utf8_bin DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `edit_time` int(11) DEFAULT NULL,
  `edit_user_id` int(11) DEFAULT NULL,
  `edit_reason` varchar(260) COLLATE utf8_bin DEFAULT NULL,
  `edit_count` int(3) DEFAULT NULL,
  `edit_locked` int(1) DEFAULT NULL,
  `data` longtext COLLATE utf8_bin,
  `post_title` varchar(260) COLLATE utf8_bin DEFAULT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT '0',
  `hashtags` varchar(250) COLLATE utf8_bin NOT NULL,
  `solved` tinyint(4) NOT NULL DEFAULT '0',
  `display_order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `privmsg`
--

DROP TABLE IF EXISTS `privmsg`;
CREATE TABLE `privmsg` (
  `id` int(11) NOT NULL,
  `msg_id` int(11) DEFAULT NULL,
  `ip` varchar(30) COLLATE utf8_bin DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  `bbcode` int(1) DEFAULT NULL,
  `edit_time` int(11) DEFAULT NULL,
  `edit_reason` varchar(260) COLLATE utf8_bin DEFAULT NULL,
  `edit_count` int(3) DEFAULT NULL,
  `data` longtext COLLATE utf8_bin,
  `post_title` varchar(260) COLLATE utf8_bin DEFAULT NULL,
  `sender` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `privmsg_to`
--

DROP TABLE IF EXISTS `privmsg_to`;
CREATE TABLE `privmsg_to` (
  `id` int(11) NOT NULL,
  `msg_id` int(11) NOT NULL,
  `receiver` int(11) NOT NULL,
  `read_time` int(11) NOT NULL DEFAULT '0',
  `message` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `ranks`
--

DROP TABLE IF EXISTS `ranks`;
CREATE TABLE `ranks` (
  `id` int(10) UNSIGNED NOT NULL,
  `image` varchar(260) COLLATE utf8_bin DEFAULT NULL,
  `name` varchar(260) COLLATE utf8_bin DEFAULT NULL,
  `special` tinyint(1) UNSIGNED DEFAULT NULL,
  `required_posts` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `report`
--

DROP TABLE IF EXISTS `report`;
CREATE TABLE `report` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `reporter` int(11) NOT NULL,
  `closer` int(11) NOT NULL,
  `close_time` int(11) NOT NULL,
  `message` varchar(512) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `session_id` varchar(15) COLLATE utf8_bin NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(15) COLLATE utf8_bin NOT NULL,
  `user_agent` text COLLATE utf8_bin NOT NULL,
  `start` int(11) NOT NULL,
  `end` int(11) NOT NULL,
  `last_seen` int(11) NOT NULL,
  `hide` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `topic`
--

DROP TABLE IF EXISTS `topic`;
CREATE TABLE `topic` (
  `topic_id` int(11) NOT NULL,
  `forum_id` int(11) DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  `Poster` int(11) DEFAULT NULL,
  `Views` int(11) DEFAULT NULL,
  `Replies` int(11) DEFAULT NULL,
  `type` int(2) DEFAULT NULL,
  `title` varchar(260) COLLATE utf8_bin DEFAULT NULL,
  `last_post_time` int(11) DEFAULT NULL,
  `last_post_id` int(11) DEFAULT NULL,
  `first_post_id` int(11) DEFAULT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT '0',
  `locked` tinyint(4) NOT NULL DEFAULT '0',
  `solved` tinyint(1) NOT NULL DEFAULT '0',
  `hidden` tinyint(1) NOT NULL,
  `display_order` int(11) NOT NULL DEFAULT '0',
  `poster_name` varchar(255) COLLATE utf8_bin NOT NULL,
  `poster_color` varchar(7) COLLATE utf8_bin NOT NULL,
  `last_poster` int(11) NOT NULL,
  `last_poster_name` varchar(255) COLLATE utf8_bin NOT NULL,
  `last_poster_color` varchar(7) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `user_password` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `salt` varchar(10) COLLATE utf8_bin NOT NULL,
  `user_email` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `user_facebook` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `user_avatar` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `user_join_date` int(11) DEFAULT NULL,
  `user_post_count` int(11) DEFAULT NULL,
  `user_default_group` int(11) DEFAULT NULL,
  `user_show_facebook` int(1) DEFAULT NULL,
  `user_show_mail` int(1) DEFAULT NULL,
  `user_warn` int(1) DEFAULT NULL,
  `user_founder` int(1) DEFAULT NULL,
  `user_rank` int(11) DEFAULT NULL,
  `user_signature` longtext COLLATE utf8_bin,
  `user_color` varchar(7) COLLATE utf8_bin NOT NULL,
  `cover` varchar(512) COLLATE utf8_bin NOT NULL,
  `about` longtext COLLATE utf8_bin NOT NULL,
  `cover_h_offset` int(11) NOT NULL,
  `last_active` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `user_groups`
--

DROP TABLE IF EXISTS `user_groups`;
CREATE TABLE `user_groups` (
  `user_id` int(11) DEFAULT NULL,
  `user_group_id` int(11) DEFAULT NULL,
  `user_status` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `warn`
--

DROP TABLE IF EXISTS `warn`;
CREATE TABLE `warn` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `message` text COLLATE utf8_bin NOT NULL,
  `points` smallint(4) NOT NULL,
  `type` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attachments`
--
ALTER TABLE `attachments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bans`
--
ALTER TABLE `bans`
  ADD PRIMARY KEY (`ban_id`);

--
-- Indexes for table `bbcode`
--
ALTER TABLE `bbcode`
  ADD PRIMARY KEY (`bbcode_id`);

--
-- Indexes for table `drafts`
--
ALTER TABLE `drafts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `forms`
--
ALTER TABLE `forms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `forum`
--
ALTER TABLE `forum`
  ADD PRIMARY KEY (`forum_id`);

--
-- Indexes for table `general`
--
ALTER TABLE `general`
  ADD UNIQUE KEY `setting` (`setting`),
  ADD UNIQUE KEY `setting_2` (`setting`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `input`
--
ALTER TABLE `input`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `log`
--
ALTER TABLE `log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`module_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`permission_id`);

--
-- Indexes for table `post`
--
ALTER TABLE `post`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `privmsg`
--
ALTER TABLE `privmsg`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `privmsg_to`
--
ALTER TABLE `privmsg_to`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ranks`
--
ALTER TABLE `ranks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `report`
--
ALTER TABLE `report`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `topic`
--
ALTER TABLE `topic`
  ADD PRIMARY KEY (`topic_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `warn`
--
ALTER TABLE `warn`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attachments`
--
ALTER TABLE `attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `bans`
--
ALTER TABLE `bans`
  MODIFY `ban_id` int(8) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `bbcode`
--
ALTER TABLE `bbcode`
  MODIFY `bbcode_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `drafts`
--
ALTER TABLE `drafts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `forms`
--
ALTER TABLE `forms`
  MODIFY `id` int(15) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `forum`
--
ALTER TABLE `forum`
  MODIFY `forum_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `input`
--
ALTER TABLE `input`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `log`
--
ALTER TABLE `log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `module_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `permission_id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `post`
--
ALTER TABLE `post`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `privmsg`
--
ALTER TABLE `privmsg`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `privmsg_to`
--
ALTER TABLE `privmsg_to`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ranks`
--
ALTER TABLE `ranks`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `report`
--
ALTER TABLE `report`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `topic`
--
ALTER TABLE `topic`
  MODIFY `topic_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `warn`
--
ALTER TABLE `warn`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
