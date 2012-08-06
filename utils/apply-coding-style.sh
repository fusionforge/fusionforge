#! /bin/sh

# EXPERIMENTAL script to enforce coding style guidelines
# There are still things to fix
# DO NOT APPLY BLINDLY!

# Things to fix:

# * Indentation is wrong inside parenthesis (next line starts with an
#  extra tab rather than aligned with the first element in the paren)
# * =& becomes = &

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
diff --git a/Beautifier/Filter/Default.filter.php b/Beautifier/Filter/Default.filter.php
index 3df2578..857b584 100755
--- a/Beautifier/Filter/Default.filter.php
+++ b/Beautifier/Filter/Default.filter.php
@@ -49,6 +49,7 @@
 final class PHP_Beautifier_Filter_Default extends PHP_Beautifier_Filter
 {
     protected $sDescription = 'Default Filter for PHP_Beautifier';
+    private $paren_level = 0;
     /**
      * __call 
      * 
@@ -192,6 +193,8 @@ final class PHP_Beautifier_Filter_Default extends PHP_Beautifier_Filter
     function t_parenthesis_open($sTag) 
     {
         $this->oBeaut->add($sTag);
+	$this->oBeaut->incIndent();
+	$this->paren_level++;
     }
     /**
      * t_parenthesis_close 
@@ -210,7 +213,8 @@ final class PHP_Beautifier_Filter_Default extends PHP_Beautifier_Filter
         if (!$this->oBeaut->isNextTokenContent(';')) {
             $this->oBeaut->add(' ');
         }
-        
+	$this->oBeaut->decIndent();
+	$this->paren_level--;
     }
     /**
      * t_open_brace 
@@ -231,9 +235,6 @@ final class PHP_Beautifier_Filter_Default extends PHP_Beautifier_Filter
                 $this->oBeaut->add($sTag);
             }
             $this->oBeaut->incIndent();
-            if ($this->oBeaut->getControlSeq() == T_SWITCH) {
-                $this->oBeaut->incIndent();
-            }
             $this->oBeaut->addNewLineIndent();
         }
     }
@@ -254,9 +255,6 @@ final class PHP_Beautifier_Filter_Default extends PHP_Beautifier_Filter
         } else {
             $this->oBeaut->removeWhitespace();
             $this->oBeaut->decIndent();
-            if ($this->oBeaut->getControlSeq() == T_SWITCH) {
-                $this->oBeaut->decIndent();
-            }
             $this->oBeaut->addNewLineIndent();
             $this->oBeaut->add($sTag);
             if ($this->oBeaut->getControlSeq() == T_DO) {
@@ -318,7 +316,11 @@ final class PHP_Beautifier_Filter_Default extends PHP_Beautifier_Filter
     function t_whitespace($sTag) 
     {
         $matches = "";
-        $minNL = 2;
+	if ($this->paren_level) {
+		$minNL = 1;
+	} else {
+		$minNL = 2;
+	}
         if($this->oBeaut->isPreviousTokenConstant(T_COMMENT)) {
             $prevToken = $this->oBeaut->getPreviousTokenContent(1);
             $tokenEnd = substr($prevToken,strlen($prevToken)-2);
@@ -678,10 +680,8 @@ final class PHP_Beautifier_Filter_Default extends PHP_Beautifier_Filter
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
diff --git a/Beautifier/Filter/IndentStyles.filter.php b/Beautifier/Filter/IndentStyles.filter.php
index fa98c45..ee57eb3 100755
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
