INSERT INTO `joebloggs_posts` VALUES (newpostid,1,'postdate','pdgmt','Personal tutor to agree the following with you:\r\n<ul>\r\n	<li>a) any goal set at initial meeting which has not yet been achieved should be transferred/modified to enable you to achieve it</li>\r\n	<li>a) if all goals set at initial meeting have been achieved please set up to 4 more</li>\r\n</ul>\r\n<table width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"7\">\r\n<tbody>\r\n<tr valign=\"top\">\r\n<th>Goal</th>\r\n<th>Achieved ?</th>\r\n<th>Date</th>\r\n</tr>\r\n<tr>\r\n<td>1)</td>\r\n<td>.</td>\r\n<td>.</td>\r\n</tr>\r\n<tr>\r\n<td>2)</td>\r\n<td>.</td>\r\n<td>.</td>\r\n</tr>\r\n<tr>\r\n<td>3)</td>\r\n<td>.</td>\r\n<td>.</td>\r\n</tr>\r\n<tr>\r\n<td>4)</td>\r\n<td>.</td>\r\n<td>.</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\nAny goals not achieved should be carried forward to 3rd meeting goals\r\n\r\nHave your ratings changed? If so, can you explain why? If not can you think of the reasons why?','ILP 2nd Meeting Goals - Session sessionyears','','draft','closed','closed','','','','','pdgmt','pdgmt','',0,'interneteproot/joebloggs/?p=newpostid',0,'post','',0);


INSERT INTO `joebloggs_term_relationships` (`object_id`, `term_taxonomy_id`, `term_order`) VALUES
(newpostid, 4, 0);

INSERT INTO `joebloggs_uam_accessgroup_to_object` (`object_id`, `object_type`, `group_id`) VALUES
('newpostid', 'post', 1),
('newpostid', 'post', 2);

INSERT INTO `joebloggs_postmeta` (`meta_id`, `post_id`, `meta_key`, `meta_value`) VALUES
(NULL, newpostid, '_edit_last', '1'),
(NULL, newpostid, '_edit_lock', '1393496252:1');
