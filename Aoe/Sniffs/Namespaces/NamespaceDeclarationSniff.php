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

class Aoe_Sniffs_Namespaces_NamespaceDeclarationSniff implements \PHP_CodeSniffer_Sniff
{
    /**
     * @return array
     */
    public function register()
    {
        return array(T_NAMESPACE, T_OPEN_TAG);
    }

    /**
     * @param \PHP_CodeSniffer_File $phpCsFile
     * @param integer $stackPtr
     */
    public function process(\PHP_CodeSniffer_File $phpCsFile, $stackPtr)
    {
        if ($this->isUndefinedFile($phpCsFile, $stackPtr))
        {
            return;
        }
        $tokens = $phpCsFile->getTokens();
        $this->checkIfNamespaceExists($phpCsFile, $stackPtr);
        $this->checkNamespaceDefinition($phpCsFile, $tokens, $stackPtr);
    }

    /**
     * @param \PHP_CodeSniffer_File $phpCsFile
     * @param integer $stackPtr
     */
    private function checkIfNamespaceExists(\PHP_CodeSniffer_File $phpCsFile, $stackPtr){
        $hasNamespace = $phpCsFile->findNext(T_NAMESPACE,0);
        if(!$hasNamespace)
        {
            $error = 'There is no namespace defined in this file.';
            $phpCsFile->addError($error, $stackPtr);
        }
    }

    /**
     * @param PHP_CodeSniffer_File $phpCsFile
     * @param integer $stackPtr
     * @return bool
     */
    private function isUndefinedFile(\PHP_CodeSniffer_File $phpCsFile, $stackPtr)
    {
        if ($phpCsFile->findNext(array(T_CLASS, T_INTERFACE, T_ABSTRACT),$stackPtr) === false)
        {
            return true;
        }
        return false;
    }

    /**
     * @param \PHP_CodeSniffer_File $phpCsFile
     * @param array $tokens
     * @param integer $stackPtr
     */
    private function checkNamespaceDefinition(PHP_CodeSniffer_File $phpCsFile, $tokens, $stackPtr)
    {
        $token = $tokens[$stackPtr];

        if($token['content'] === 'namespace' and $token['line'] !== 2)
        {
            $error = 'The namespace is not placed directly behind the opening php-tag.
                Found at line: %s. But should be line: 2';
            $line = array(trim($tokens[$stackPtr]['line']));
            $phpCsFile->addWarning($error, $stackPtr, 'Found', $line);
        }
    }
}