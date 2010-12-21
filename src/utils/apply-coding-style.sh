#! /bin/sh

# EXPERIMENTAL script to enforce coding style guidelines
# There are still things to fix
# DO NOT APPLY BLINDLY!

# Things to fix:
# * long lines (such as appear with long lists of parameters to functions) are unwrapped
#   even when they were previously split across several lines

set -e

arg=$1
file=$(readlink -e $arg)

if [ ! -e $file ] ; then
    echo "Missing arg"
    exit 1
fi

dir=$(dirname $file)

if [ ! -x /tmp/PHP_Beautifier/scripts/php_beautifier ] ; then
    cd /tmp
    git clone https://github.com/jespino/PHP_Beautifier.git
    cd PHP_Beautifier
    git checkout 15e6c66d2b2473cd3487c86ab9b2e3d5ed567ee7
    ln -s . PHP
    patch -p1 <<'EOF'
--- a/Beautifier/Filter/Default.filter.php
+++ b/Beautifier/Filter/Default.filter.php
@@ -231,9 +231,6 @@ final class PHP_Beautifier_Filter_Default extends PHP_Beautifier_Filter
                 $this->oBeaut->add($sTag);
             }
             $this->oBeaut->incIndent();
-            if ($this->oBeaut->getControlSeq() == T_SWITCH) {
-                $this->oBeaut->incIndent();
-            }
             $this->oBeaut->addNewLineIndent();
         }
     }
@@ -254,9 +251,6 @@ final class PHP_Beautifier_Filter_Default extends PHP_Beautifier_Filter
         } else {
             $this->oBeaut->removeWhitespace();
             $this->oBeaut->decIndent();
-            if ($this->oBeaut->getControlSeq() == T_SWITCH) {
-                $this->oBeaut->decIndent();
-            }
             $this->oBeaut->addNewLineIndent();
             $this->oBeaut->add($sTag);
             if ($this->oBeaut->getControlSeq() == T_DO) {
@@ -678,10 +672,8 @@ final class PHP_Beautifier_Filter_Default extends PHP_Beautifier_Filter
     {
         if ($this->oBeaut->getControlSeq() == T_SWITCH) {
             $this->oBeaut->removeWhitespace();
-            $this->oBeaut->decIndent();
             $this->oBeaut->addNewLineIndent();
             $this->oBeaut->add($sTag);
-            $this->oBeaut->incIndent();
         } else {
             $this->oBeaut->add($sTag);
         }
--- a/Beautifier/Filter/IndentStyles.filter.php
+++ b/Beautifier/Filter/IndentStyles.filter.php
@@ -265,7 +265,7 @@ class PHP_Beautifier_Filter_IndentStyles extends PHP_Beautifier_Filter
     {
         if ($this->oBeaut->getPreviousTokenContent() == '}') {
             $this->oBeaut->removeWhitespace();
-            $this->oBeaut->addNewLineIndent();
+            $this->oBeaut->add(' ');
             $this->oBeaut->add(trim($sTag));
             if (!$this->oBeaut->isNextTokenContent('{')) {
                     $this->oBeaut->add(' ');
EOF
fi

cd /tmp/PHP_Beautifier
php scripts/php_beautifier --filters "IndentStyles(style=k&r)" -t 1 -v $file $dir/
