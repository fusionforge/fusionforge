#! /bin/sh

# EXPERIMENTAL script to enforce coding style guidelines
# There are still things to fix (including the removal of too many newlines)
# DO NOT APPLY BLINDLY!

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
    git checkout remotes/origin/whitespaces
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
EOF
fi

cd /tmp/PHP_Beautifier
php scripts/php_beautifier --filters "IndentStyles(style=k&r)" -t 1 -v $file $dir/
