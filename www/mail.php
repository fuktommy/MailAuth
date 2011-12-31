<?php
/* MailAuth sample.
 *
 * Copyright (c) 2012 Satoshi Fukutomi <info@fuktommy.com>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHORS AND CONTRIBUTORS ``AS IS'' AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED.  IN NO EVENT SHALL THE AUTHORS OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS
 * OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
 * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
 * SUCH DAMAGE.
 */

require_once(dirname(__FILE__) . '/../lib/bootstrap.php');

if (empty($_GET['mail'])) {
    header('Location: ./');
    return;
}

$mail = $_GET['mail'];
$key = generateKey();
$entry = new MailAuth_Entry($mail, $key);

$storage = new MailAuth_Storage_Sqlite(getPdo());
$storage->add($entry);

$m = ModifireChain::factory();
$m->mail = $mail;
$m->key = $key;

function generateKey()
{
    $r = array();
    for ($i = 0; $i < 10; $i++) {
        $r[] = (string)mt_rand();
    }
    return sha1(implode(':', $r));
}


?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
  <title>MailAuth sample</title>
</head>
<body>
  <div>
    I sent a mail to <?php $m->mail->e(); ?>. Check it.<br>
    But this sample send no mails.<br>
    Click <a href="./auth.php?key=<?php $m->key->e('url'); ?>">this link</a>.
  <div>
</body>
</html>
