<?php
return array (
  'delay' => NULL,
  'signature' => '96dd4d1ade4fac3d31b899c7bf919144',
  'data' => 
  array (
    'driver' => 'mysql',
    'sql' => 'SELECT id,noms FROM membres_msbc  UNION ALL  SELECT  id,noms FROM enfant   UNION ALL  SELECT  id,noms FROM conjointes   UNION ALL  SELECT  id,noms FROM amis   UNION ALL  SELECT  id,noms FROM temoins   UNION ALL  SELECT  id,noms FROM prenoms_alternatifs   UNION ALL  SELECT  id,noms FROM contacts_urgents   UNION ALL  SELECT  id,noms FROM historique_adresses   UNION ALL  SELECT  id,noms FROM profils_sociaux   UNION ALL  SELECT  id,noms FROM documents_joints   UNION ALL  SELECT  id,noms FROM notifications  ',
    'params' => 
    array (
    ),
  ),
);