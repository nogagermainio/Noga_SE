<?php
return array (
  'delay' => NULL,
  'signature' => 'cfa29f9603eef9d1257c244964e90ddf',
  'data' => 
  array (
    'driver' => 'mysql',
    'sql' => 'WITH RECURSIVE user_cte AS (SELECT id,noms,prenoms,titre FROM membres_msbc  UNION ALL  SELECT  id,noms,prenoms,titre FROM conjointes   UNION ALL  SELECT  id,noms,prenoms,titre FROM enfant  ) SELECT * FROM user_cte  LIMIT 3 ',
    'params' => 
    array (
    ),
  ),
);