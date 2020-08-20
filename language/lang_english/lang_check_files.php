<?php

$lang['to_low_php'] = 'too low version.';
$lang['cookie_different'] = 'Server name differs from Cookie domain.';
$lang['is_writable'] = 'is writable';
$lang['not_writable'] = 'is not writable';
$lang['set_chmod'] = 'Try to set by shell or FTP:';
$lang['function_ct_not_exists'] = 'function creating thumbnails dosn\'t exist !</font> That means GD library is not loaded.';
$lang['gd_loaded'] = 'function exists, GD loaded';
$lang['function_gd'] = 'function exists, GD <b>[ %s ]</b> loaded';
$lang['function_zlip_not_exists'] = 'zlib dosn\'t loaded !.</font> That means validate graphic register and other may not work.';
$lang['loaded'] = 'loaded';
$lang['fcr'] = 'Files checksum result:';
$lang['wrong_content'] = 'Wrong content';
$lang['file_missing'] = 'File missing';
$lang['result_e'] = 'Some files have wrong content. If you\'ve edited any files be aware that they may contain errors. If you haven\'t edited any of them that means you have some problems with your FTP upload. Try to upload these files in binary mode.';
$lang['all_files_ok'] = 'All %s files OK';

$lang['checksum_current'] = 'current checksum';
$lang['checksum_correct'] = 'correct checksum';

$lang['cf_only_admin'] = 'Only admin can view CheckFiles.';
$lang['Wrong_sql_version'] = 'Database version';
$lang['version'] = 'version';
$lang['sql_checkng'] = 'Check SQL synchronization';
$lang['admin_explain'] = 'If you want allow to CheckFiles only admins, change value of variable <b>$only_admin</b> to <b>1</b> begin of the check_files.php<br />But remember ! If you go to ask about problem/question with your forum, allow us to CheckFiles !';

$lang['user_posts'] = 'User posts';
$lang['post_text'] = 'Posts&nbsp;text';
$lang['topic_posts'] = 'Posts&nbsp;in&nbsp;topics';
$lang['topic_first_last_post'] = 'First&nbsp;and&nbsp;last&nbsp;post&nbsp;in&nbsp;topics';
$lang['moved_topics'] = 'Moved&nbsp;topics';
$lang['topic_without_posts'] = 'Topics&nbsp;without&nbsp;posts';
$lang['post_without_topics'] = 'Posts&nbsp;without&nbsp;topics';
$lang['forums_posts'] = 'Posts&nbsp;in&nbsp;forums';
$lang['forums_topics'] = 'Topics&nbsp;in&nbsp;forums';
$lang['forums_last_post'] = 'Last&nbsp;forums&nbsp;post';
$lang['polls'] = 'Polls';
$lang['users'] = 'Users';
$lang['all'] = 'All';
$lang['sync_explain'] = '(This operation can take more time and much load server for huge databases!)';
$lang['back'] = 'Back';
$lang['CF_back'] = 'Back to CheckFiles';
$lang['users_wrong_posts'] = 'Users with wrong post count';
$lang['non_exists_posters'] = 'Non exists posters';
$lang['non_exists_topic_authors'] = 'Non exists topic authors';
$lang['posts_was_sync'] = '<b>Posts have been synchronized, once more check the total post synchronization.<br /></b>If the result is negative, start the synchronization in your Administrator\'s Control Panel.';
$lang['empty_posts'] = 'ID "empty" poss without text';
$lang['empty_posts_text'] = 'ID posts text';
$lang['delete_empty_posts'] = 'Remove "empty" posts and texts without posts';
$lang['topics_wrong_replies'] = 'Topics with wrong value of replies';
$lang['topics_wrong_last_post'] = 'Topics with wrong last post ID';
$lang['topics_wrong_first_post'] = 'Topics with wrong first post ID';
$lang['empty_moved_topics'] = '"Empty" moved topics (for remove) without equivalent';
$lang['topics_was_sync'] = '<b>Topics have been synchronized, once more check the total post synchronization (whole board).<br /></b>If the result is negative, start the synchronization in your Administrator\'s Control Panel. ';
$lang['empty_topics'] = 'Temat�w nie przypisanych do �adnego lub odpowiedniego forum: <b>%s</b><br />[Topic ID - Forum ID]: %s<br />Kliknij %sTutaj%s aby usun�� te tematy.';
$lang['forums_wrong_posts'] = 'Forums with wrong post count';
$lang['forums_wrong_topics'] = 'Forums with wrong topics count';
$lang['forums_wrong_last_post'] = 'Forums with wrong last post ID';
$lang['polls_without_topics'] = 'Polls without topics';
$lang['votes_without_polls'] = 'Votes with no poll';
$lang['voters_without_polls'] = 'Voters with no poll';
$lang['users_without_groups'] = 'Users without private group: <b>%s</b><br />User ID\'s: %s<br /><font size="1">';
$lang['SQL_unsync'] = 'SQL database is not synchronize !';
$lang['SQL_unsync_e'] = 'Tools for synchronizing your database can be found in the Administrator\'s Control Panel  in sections : Forum Admin, User Admin and Attachements</b><br /><font size="1">Desynchronization doesn\'t bother the forum working properly:<br />- User posts (this could be caused for example by setting a subforum as one that doesn\'t count posts and starting new threads in it)<br />- The difference in the amount and content of posts under the condition that the amount of content in posts is bigger<br />&nbsp;&nbsp;(usually it\'s the reason of manual editing the database or an unstable server)<br />- Polls not assigned to threads<br />Other desynchronizations should be eliminated as quick as possible.</font> ';
$lang['SQL_sync'] = 'SQL database is synchronized';
$lang['sync'] = 'Synchronized';
$lang['gentime'] = 'Checking time';
$lang['SQL_queries'] = 'SQL queries';
$lang['check_SQL'] = 'Check SQL database synchronization';
$lang['domain_name'] = 'Domain name';
$lang['domain_name_wrong'] = 'Domain Name and Cookie Domain is not same !';
$lang['gzip'] = 'Forum gzip compression';
$lang['installed_mods'] = 'Mods installed';
$lang['modified'] = 'modified';
$lang['count_chr'] = 'Chars count:<br />Actually - Originally';
$lang['filename'] = 'File name';
$lang['forum_compress'] = 'script';
$lang['server_compress'] = 'server';
$lang['Missing_tables'] = 'In database missing tables';
$lang['Missing_table_fields'] = 'In table "%s" missing column';
$lang['Missing_field'] = 'In table: %s missing column';
$lang['Missing_inserts'] = 'In table: %s missing insert';
$lang['sync_unread_pms'] = 'Unread pms';
?>