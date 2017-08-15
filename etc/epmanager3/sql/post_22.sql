INSERT INTO `joebloggs_posts` VALUES (newpostid,1,'postdate','pdgmt','<table width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"7\">\r\n<tbody>\r\n<tr valign=\"top\">\r\n<th>Goal</th>\r\n<th>Achieved ?</th>\r\n<th>Date</th>\r\n</tr>\r\n<tr>\r\n<td>1)</td>\r\n<td>.</td>\r\n<td>.</td>\r\n</tr>\r\n<tr>\r\n<td>2)</td>\r\n<td>.</td>\r\n<td>.</td>\r\n</tr>\r\n<tr>\r\n<td>3)</td>\r\n<td>.</td>\r\n<td>.</td>\r\n</tr>\r\n<tr>\r\n<td>4)</td>\r\n<td>.</td>\r\n<td>.</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<p style=\"color: green;\">Any goals not achieved should be carried forward to 2nd meeting goals</p>','ILP 1st Meeting Goals - Session sessionyears','','draft','closed','closed','','','','','pdgmt','pdgmt','',0,'interneteproot/joebloggs/?p=newpostid',0,'post','',0);
/*!40000 ALTER TABLE `joebloggs_posts` ENABLE KEYS */;

INSERT INTO `joebloggs_uam_accessgroup_to_object` (`object_id`, `object_type`, `group_id`) VALUES
('newpostid', 'post', 1),
('newpostid', 'post', 2);

INSERT INTO `joebloggs_postmeta` (`meta_id`, `post_id`, `meta_key`, `meta_value`) VALUES
(NULL, newpostid, '_edit_last', '1'),
(NULL, newpostid, '_edit_lock', '1390471221:1');

INSERT INTO `joebloggs_term_relationships` (`object_id`, `term_taxonomy_id`, `term_order`) VALUES
(newpostid, 4, 0);
