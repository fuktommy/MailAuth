<?php
/* Unit test for mail address storage.
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

/**
 * Unit test for mail address storage.
 * @package MailAuth
 */
class MailAuth_Storage_SqliteTest extends PHPUnit_Framework_TestCase
{
    private function _getPdo()
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }

    public function testSelectEntryByKeyEmpty()
    {
        $storage = new MailAuth_Storage_Sqlite($this->_getPdo());
        $this->assertSame(array(), $storage->selectEntryByKey('notfound'));
    }

    public function testAdd()
    {
        $storage = new MailAuth_Storage_Sqlite($this->_getPdo());
        $storage->add(new MailAuth_Entry('mailaddress', 'keystring'));
        $this->assertSame(array(), $storage->selectEntryByKey('notfound'));

        $selected = $storage->selectEntryByKey('keystring');
        $this->assertSame(1, count($selected));
        $this->assertSame('mailaddress', $selected[0]->mail());
        $this->assertSame('keystring', $selected[0]->key());
    }

    public function testAddDuplicate()
    {
        $storage = new MailAuth_Storage_Sqlite($this->_getPdo());
        $storage->add(new MailAuth_Entry('mailaddress_1', 'keystring'));
        $storage->add(new MailAuth_Entry('mailaddress', 'keystring_1'));
        $storage->add(new MailAuth_Entry('mailaddress', 'keystring'));

        $this->assertSame(array(), $storage->selectEntryByKey('keystring_1'));

        $selected = $storage->selectEntryByKey('keystring');
        $this->assertSame(1, count($selected));
        $this->assertSame('mailaddress', $selected[0]->mail());
        $this->assertSame('keystring', $selected[0]->key());
    }

    public function testDelete()
    {
        $storage = new MailAuth_Storage_Sqlite($this->_getPdo());
        $storage->add(new MailAuth_Entry('mailaddress', 'keystring'));
        $storage->add(new MailAuth_Entry('mailaddress_1', 'keystring_1'));
        $storage->add(new MailAuth_Entry('mailaddress_2', 'keystring_2'));
        $storage->delete(new MailAuth_Entry('mailaddress_1', 'keystring_2'));

        $selected = $storage->selectEntryByKey('keystring');
        $this->assertSame(1, count($selected));
        $this->assertSame('mailaddress', $selected[0]->mail());
        $this->assertSame('keystring', $selected[0]->key());

        $this->assertSame(array(), $storage->selectEntryByKey('keystring_1'));
        $this->assertSame(array(), $storage->selectEntryByKey('keystring_2'));
    }

    public function testCleanup()
    {
        $db = $this->_getPdo();

        $storage = new MailAuth_Storage_Sqlite($db);
        $storage->add(new MailAuth_Entry('mailaddress', 'keystring'));
        $storage->add(new MailAuth_Entry('mailaddress_1', 'keystring_1'));

        $state = $db->prepare("UPDATE `mailmap` SET `date` = :date WHERE `mail` = 'mailaddress_1'");
        $state->execute(array('date' => strftime('%Y-%m-%d %H:%M:%s', time() - 15 * 60)));
        $storage->cleanup();

        $selected = $storage->selectEntryByKey('keystring');
        $this->assertSame(1, count($selected));
        $this->assertSame('mailaddress', $selected[0]->mail());
        $this->assertSame('keystring', $selected[0]->key());

        $this->assertSame(array(), $storage->selectEntryByKey('keystring_1'));
    }
}
