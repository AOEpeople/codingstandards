<?php

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/*********************************
 *  Copyright notice
 *
 *  (c) 2018 AOE GmbH <dev@aoe.com>
 *  All rights reserved
 *
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

class Aoe_Sniffs_CodeCoverageIgnore_CodeCoverageIgnoreSniff implements Sniff
{
    /**
     * @var array
     */
    private $tagTypes = array(
        '@codeCoverageIgnoreStart',
        '@codeCoverageIgnoreEnd',
        '@codeCoverageIgnore'
    );

    /**
     * @return array
     */
    public function register()
    {
        return array(T_OPEN_TAG);
    }

    /**
     * @param File $phpCsFile
     * @param integer $stackPtr
     */
    public function process(File $phpCsFile, $stackPtr)
    {
        $tokens = $phpCsFile->getTokens();
        foreach ($tokens as $token) {
            if ($token['code'] === T_DOC_COMMENT || T_COMMENT) {
                $this->checkIfTokenIsCodeIgnoreAnnotation($phpCsFile, $token);
            }
        }
    }

    /**
     * @param File $phpCsFile
     * @param array $token
     */
    private function checkIfTokenIsCodeIgnoreAnnotation(File $phpCsFile, $token)
    {
        $tagType = $this->getTagType($token['content']);
        switch ($tagType) {
            case 0:
                $errorMessage = $this->tagTypes[0] . "-tag detected at line: " . $token['line'];
                $phpCsFile->addWarningOnLine($errorMessage, $token['line'], '1545122516');
                break;
            case 1:
                $errorMessage = $this->tagTypes[1] . "-tag detected at line: " . $token['line'];
                $phpCsFile->addWarningOnLine($errorMessage, $token['line'], '1545122537');
                break;
            case 2:
                $errorMessage = $this->tagTypes[2] . "-tag detected at line: " . $token['line'];
                $phpCsFile->addWarningOnLine($errorMessage, $token['line'], '1545122545');
                break;
        }
    }

    /**
     * @param string $content
     * @return integer tagType
     *
     */
    private function getTagType($content)
    {
        $hit = [];
        $match = false;

        foreach ($this->tagTypes as $pattern) {
            $match = preg_match("/" . $pattern . "/", $content, $hit);
            if ($match === 1) {
                break;
            }
        }

        if ($match === 0 || $match === false) {
            return -1;
        }

        switch ($hit[0]) {
            case $this->tagTypes[0]:
                return 0;
                break;
            case $this->tagTypes[1]:
                return 1;
                break;
            case $this->tagTypes[2]:
                return 2;
                break;

        }

        return -1;
    }
}