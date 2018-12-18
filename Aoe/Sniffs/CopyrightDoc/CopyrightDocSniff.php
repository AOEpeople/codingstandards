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

class Aoe_Sniffs_CopyrightDoc_CopyrightDocSniff implements Sniff
{
    /**
     * @var integer
     */
    private $copyrightDocLineIndex;

    /**
     * @return array
     */
    public function register()
    {
        return array(T_CLASS, T_INTERFACE, T_ABSTRACT);
    }

    /**
     * @param File $phpCsFile
     * @param integer $stackPtr
     */
    public function process(File $phpCsFile, $stackPtr)
    {
        if ($this->isUndefinedFile($phpCsFile, $stackPtr)) {
            return;
        }
        $tokens = $phpCsFile->getTokens();
        $this->checkIfFileDocExists($phpCsFile, $tokens);
        if ($this->copyrightDocLineIndex !== false) {
            $this->checkDocPosition($phpCsFile, $tokens);
        }
    }

    /**
     * @param File $phpCsFile
     * @param integer $stackPtr
     * @return bool
     */
    private function isUndefinedFile(File $phpCsFile, $stackPtr)
    {
        if ($phpCsFile->findNext(array(T_CLASS, T_INTERFACE, T_ABSTRACT),$stackPtr) === false) {
            return true;
        }

        return false;
    }

    /**
     * @param File $phpCsFile
     * @param array $tokens
     */
    private function checkIfFileDocExists(File $phpCsFile, $tokens)
    {
        $i = 0;
        $regexFound = false;
        $copyrightNoteFound = false;
        do {
            if (preg_match('/^\/\*\*[\*]*/', $tokens[$i]['content'], $matches) === 1) {
                $regexFound = true;
                $copyrightNoteFound = $this->checkIfCopyrightNoteExists($tokens, $i);
                break;
            }
            $i = $i + 1;
        } while(
            $tokens[$i]['code'] !== T_CLASS &&
            $tokens[$i]['code'] !== T_INTERFACE &&
            $tokens[$i]['code'] !== T_ABSTRACT
        );

        if ($regexFound === false) {
            $phpCsFile->addWarning('Missing file doc comment', $i, 'Missing');
        }

        if ($regexFound === true && $copyrightNoteFound === false) {
            $phpCsFile->addWarning('Missing copyright description', $i, 'Missing');
        }
    }

    /**
     * @param File $phpCsFile
     * @param array $tokens
     */
    private function checkDocPosition(File $phpCsFile, $tokens)
    {
        $i = 0;
        $namespaceLineIndex = 0;
        $namespaceFound = false;
        do {
            if ($tokens[$i]['code'] === T_NAMESPACE) {
                $namespaceFound = true;
                $namespaceLineIndex = $tokens[$i]['line'];
                break;
            }
            $i = $i + 1;
        } while(
            $tokens[$i]['code'] !== T_CLASS &&
            $tokens[$i]['code'] !== T_INTERFACE &&
            $tokens[$i]['code'] !== T_ABSTRACT
        );

        if ($namespaceFound === true && $namespaceLineIndex > $this->copyrightDocLineIndex) {
            $phpCsFile->addWarning(
                'File doc comment should be defined after the namespace',
                $this->copyrightDocLineIndex,
                'wrong order'
            );
        }
    }

    /**
     * @param array $tokens
     * @param integer $i token index
     * @return bool
     */
    private function checkIfCopyrightNoteExists($tokens, $i)
    {
        do {
            $i = $i + 1;
            if (preg_match('/Copyright/', $tokens[$i]['content'], $matches) === 1) {
                $this->copyrightDocLineIndex = ($tokens[$i]['line'] - 1);

                return true;
            }
        } while ($tokens[$i]['code'] === T_COMMENT);

        return false;
    }
}