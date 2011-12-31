<?php
/* Database Mygration.
 *
 * Copyright (c) 2010 Satoshi Fukutomi <info@fuktommy.com>.
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
 * Database Mygration.
 */
class Migration
{
    /**
     * @var PDO
     */
    private $db;

    /**
     * Constructor.
     * @param PDO $db
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `mygration`"
            . " (`query` TEXT PRIMARY KEY NOT NULL, `date` CHAR NOT NULL)"
        );
    }

    private function includes($sql)
    {
        $count = $this->db->prepare(
            "SELECT COUNT(*) FROM `mygration` WHERE `query` = :query"
        );
        $count->execute(array('query' => $sql));
        return (bool)(int)$count->fetchColumn();
    }

    private function append($sql)
    {
        $append = $this->db->prepare(
            "INSERT INTO `mygration` (`query`, `date`) VALUES (:query, :date)"
        );
        $append->execute(array(
            'query' => $sql,
            'date' => gmstrftime('%Y-%m-%dT%H:%M:%SZ'),
        ));
    }

    /**
     * Execute querty.
     * @param string $sql
     * @throws PDOException
     */
    public function execute($sql)
    {
        if ($this->includes($sql)) {
            return;
        }
        $this->db->query($sql);
        $this->append($sql);
    }
}
