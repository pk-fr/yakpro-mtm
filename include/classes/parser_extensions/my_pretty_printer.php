<?php
//========================================================================
// Author:  Pascal KISSIAN
// Resume:  http://pascal.kissian.net
//
// Copyright (c) 2015 Pascal KISSIAN
//
// Published under the MIT License
//          Consider it as a proof of concept!
//          No warranty of any kind.
//          Use and abuse at your own risks.
//========================================================================

class myPrettyprinter extends PhpParser\PrettyPrinter\Standard
{
    public function pStmt_If(PhpParser\Node\Stmt\If_ $node)
    {
        return 'if (' . $this->p($node->cond) . ")\n{"
             . $this->pStmts($node->stmts) . "\n" . '}'
             . $this->pImplode($node->elseifs)
             . (null !== $node->else ? $this->p($node->else) : '');
    }

    public function pStmt_ElseIf(PhpParser\Node\Stmt\ElseIf_ $node) {
        return "\nelseif (" . $this->p($node->cond) . ")\n{"
             . $this->pStmts($node->stmts) . "\n" . '}';
    }

    public function pStmt_Else(PhpParser\Node\Stmt\Else_ $node) {
        return "\nelse\n{" . $this->pStmts($node->stmts) . "\n" . '}';
    }


    public function pStmt_For(PhpParser\Node\Stmt\For_ $node) {
        return 'for ('
             . $this->pCommaSeparated($node->init) . ';' . (!empty($node->cond) ? ' ' : '')
             . $this->pCommaSeparated($node->cond) . ';' . (!empty($node->loop) ? ' ' : '')
             . $this->pCommaSeparated($node->loop)
             . ")\n{" . $this->pStmts($node->stmts) . "\n" . '}';
    }

    public function pStmt_Foreach(PhpParser\Node\Stmt\Foreach_ $node) {
        return 'foreach (' . $this->p($node->expr) . ' as '
             . (null !== $node->keyVar ? $this->p($node->keyVar) . ' => ' : '')
             . ($node->byRef ? '&' : '') . $this->p($node->valueVar) . ")\n{"
             . $this->pStmts($node->stmts) . "\n" . '}';
    }

    public function pStmt_While(PhpParser\Node\Stmt\While_ $node) {
        return 'while (' . $this->p($node->cond) . ")\n{"
             . $this->pStmts($node->stmts) . "\n" . '}';
    }

    public function pStmt_Do(PhpParser\Node\Stmt\Do_ $node) {
        return "do\n{" . $this->pStmts($node->stmts) . "\n"
             . "} while (" . $this->p($node->cond) . ');';
    }


    public function pStmt_Switch(PhpParser\Node\Stmt\Switch_ $node) {
        return 'switch (' . $this->p($node->cond) . ")\n{"
             . $this->pStmts($node->cases) . "\n" . '}';
    }

    public function pStmt_TryCatch(PhpParser\Node\Stmt\TryCatch $node) {
        return "try\n{" . $this->pStmts($node->stmts) . "\n" . '}'
             . $this->pImplode($node->catches)
             . ($node->finallyStmts !== null
                ? "\nfinally\n{" . $this->pStmts($node->finallyStmts) . "\n" . '}'
                : '');
    }

    public function pStmt_Catch(PhpParser\Node\Stmt\Catch_ $node) {
        return "\ncatch (" . $this->p($node->type) . ' $' . $node->{'var'} . ")\n{"
             . $this->pStmts($node->stmts) . "\n" . '}';
    }

    
}

?>
