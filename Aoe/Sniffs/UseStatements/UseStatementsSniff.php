<?php

/*********************************
 *  Copyright notice
 *
 *  (c) 2015 AOE GmbH <dev@aoe.com>
 *  All rights reserved
 *
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

class Aoe_Sniffs_UseStatements_UseStatementsSniff implements \PHP_CodeSniffer_Sniff
{

    /**
     * @var array
     */
    private $lineIndices;

    /**
     * @return array
     */
    public function register()
    {
        return array(T_CLASS, T_ABSTRACT, T_INTERFACE);
    }

    /**
     * @param \PHP_CodeSniffer_File $phpCsFile
     * @param integer $stackPtr
     */
    public function process(\PHP_CodeSniffer_File $phpCsFile, $stackPtr)
    {
        if ($this->isUndefinedFile($phpCsFile, $stackPtr)) {
            return;
        }
        $tokens = $phpCsFile->getTokens();
        $this->lineIndices = $this->getLineIndices($tokens);
        $this->checkUseStatementPosition($phpCsFile, $tokens);
    }

    /**
     * @param PHP_CodeSniffer_File $phpCsFile
     * @param integer $stackPtr
     * @return bool
     */
    private function isUndefinedFile(\PHP_CodeSniffer_File $phpCsFile, $stackPtr)
    {
        if ($phpCsFile->findNext(array(T_CLASS, T_INTERFACE, T_ABSTRACT), $stackPtr) === false) {
            return true;
        }
        return false;
    }

    /**
     * @param PHP_CodeSniffer_File $phpCsFile
     * @param array $tokens
     */
    private function checkUseStatementPosition(\PHP_CodeSniffer_File $phpCsFile, $tokens)
    {
        $i = 0;
        do {
            if ($tokens[$i]['code'] === T_USE) {
                $useStatementLineIndex = $tokens[$i]['line'];
                $this->checkPosition($phpCsFile, $useStatementLineIndex, $i);
            }
            $i = $i + 1;
        } while (
            $tokens[$i]['code'] !== T_CLASS &&
            $tokens[$i]['code'] !== T_INTERFACE &&
            $tokens[$i]['code'] !== T_ABSTRACT
        );
    }

    /**
     * @param array $tokens
     * @return array list of line indices
     */
    private function getLineIndices($tokens)
    {
        $i = 0;
        $indices = array();
        $indices['fileDocLineIndex'] = false;
        $indices['namespaceLineIndex'] = false;
        do {
            if (preg_match('/^\/\*\*[\*]*/', $tokens[$i]['content'], $matches) === 1) {
                $tmp = $this->checkIfCopyrightNoteExists($tokens, $i);
                if ($tmp !== false) {
                    $indices['fileDocLineIndex'] = $tmp;
                }
            } else if ($tokens[$i]['code'] === T_NAMESPACE) {
                $indices['namespaceLineIndex'] = $tokens[$i]['line'];
            }
            $i = $i + 1;
        } while (
            $tokens[$i]['code'] !== T_CLASS &&
            $tokens[$i]['code'] !== T_INTERFACE &&
            $tokens[$i]['code'] !== T_ABSTRACT
        );
        return $indices;
    }

    /**
     * @param array $tokens
     * @param integer $i token index
     * @return bool | integer
     */
    private function checkIfCopyrightNoteExists($tokens, $i)
    {
        do {
            $i = $i + 1;
            if (preg_match('/Copyright/', $tokens[$i]['content'], $matches) === 1) {
                return ($tokens[$i]['line'] - 1);
            }
        } while ($tokens[$i]['code'] === T_COMMENT);
        return false;
    }

    /**
     * @param \PHP_CodeSniffer_File $phpCsFile
     * @param integer $useStatementLineIndex
     * @param integer $i
     */
    private function checkPosition($phpCsFile, $useStatementLineIndex, $i)
    {
        if (
            $this->lineIndices['fileDocLineIndex'] > $useStatementLineIndex &&
            $this->lineIndices['namespaceLineIndex'] > $useStatementLineIndex
        ) {
            $phpCsFile->addWarning(
                'use statement should be defined after the copyright doc comment
                        and after the namespace',
                $i,
                'wrong order'
            );
        } else if (
            $this->lineIndices['fileDocLineIndex'] > $useStatementLineIndex
        ) {

            $phpCsFile->addWarning(
                'use statement should be defined after the copyright doc comment',
                $i,
                'wrong order'
            );
        } else if (
            $this->lineIndices['namespaceLineIndex'] > $useStatementLineIndex
        ) {
            $phpCsFile->addWarning(
                'use statement should be defined after the namespace',
                $i,
                'wrong order'
            );
        }
    }
}