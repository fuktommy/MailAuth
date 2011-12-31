<?php
/* Mail address storage for MailAuth.
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
 * Mail address storage.
 * @package MailAuth
 */
class MailAuth_Storage_Sqlite extends MailAuth_Storage
{
    /**
     * @var PDO
     */
    private $db;

    /**
     * Constructor.
     * @param PDO $db
     * @throws PDOException
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->_setUp($db);
    }

    private function _setUp(PDO $db)
    {
        $migration = new Migration($db);
        $migration->execute(
            "CREATE TABLE IF NOT EXISTS `mailmap`"
            . " (`mail` CHAR PRIMARY KEY NOT NULL,"
            . "  `hashkey` CHAR UNIQUE NOT NULL,"
            . "  `date` CHAR NOT NULL)"
        );
        $migration->execute(
            "CREATE INDEX `hashkey` ON `mailmap` (`hashkey`)"
        );
        $migration->execute(
            "CREATE INDEX `date` ON `date` (`date`)"
        );
    }

    /**
     * Add mail address and key pair.
     * @param MailAuth_Entry $entry
     * @throws PDOException
     */
    public function add(MailAuth_Entry $entry)
    {
        $state = $this->db->prepare(
            "INSERT OR REPLACE INTO `mailmap` (`mail`, `hashkey`, `date`)"
            . " VALUES(:mail, :hashkey, :date)");
        $state->execute(array(
            'mail' => $entry->mail(),
            'hashkey' => $entry->key(),
            'date' => strftime('%Y-%m-%d %H:%M:%S'),
        ));
    }

    /**
     * Delete entry for mail address or key.
     * @param MailAuth_Entry $entry
     * @throws PDOException
     */
    public function delete(MailAuth_Entry $entry)
    {
        $state = $this->db->prepare(
            "DELETE FROM `mailmap` WHERE `mail` = :mail OR `hashkey` = :hashkey");
        $state->execute(array(
            'mail' => $entry->mail(),
            'hashkey' => $entry->key(),
        ));
    }

    /**
     * Search entry by key.
     * @param string $key
     * @return array array of MailAuth_Entry
     * @throws PDOException
     */
    public function selectEntryByKey($key)
    {
        $state = $this->db->prepare(
            "SELECT * FROM `mailmap` WHERE `hashkey` = :hashkey");
        $state->execute(array(
            'hashkey' => $key,
        ));
        $ret = array();
        foreach ($state->fetchall(PDO::FETCH_ASSOC) as $row) {
            $ret[] = new MailAuth_Entry($row['mail'], $row['hashkey'], $row['date']);
        }
        return $ret;
    }

    /**
     * Clean up old entries.
     * @throws PDOException
     */
    public function cleanup()
    {
        $state = $this->db->prepare(
            "DELETE FROM `mailmap` WHERE `date` < :date");
        $state->execute(array(
            'date' => strftime('%Y-%m-%d %H:%M:%S', time() - 10 * 60)
        ));
    }
}
