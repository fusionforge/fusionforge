<?php
/**
  *
  * SourceForge User's Personal Page
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id: intelagreement.php,v 1.16 2001/05/22 18:54:24 pfalcon Exp $
  *
  */


require_once('pre.php');

if (user_isloggedin()) {

	if ($ACCEPT) {
		//first delete any prior request
		db_query("DELETE FROM intel_agreement WHERE user_id='". user_getid() ."'");

		//insert their new request with an is_approved flag=false
		$sql="INSERT INTO intel_agreement (user_id,message,is_approved) ".
			"VALUES ('". user_getid() ."','". htmlspecialchars($message) ."','0')";
		$result=db_query($sql);
		if (!$result || db_affected_rows($result) < 1) {
			$feedback .= ' ERROR inserting data ';
			echo db_error();
		} else {
			site_user_header(array('title'=>'Agreement'));
			echo '<P>
				<H2>Received</H2>
				<P>
				Your request has been received. You will 
				receive an email when it is accepted or rejected';
			site_user_footer(array());
			exit;
		}
	} else if ($REJECT) {
		header ("Location: /my/");
	}

	site_user_header(array('title'=>'Agreement'));
	html_feedback_top($feedback);

	echo '<P>';
?>
<H2>OPEN-SOURCE CLICKWRAP IPLA</H2>
<P>
Intel would like to invite you to participate in Intel's efforts to prepare
software targeted for the Linux operating system running on the Intel(R) Itanium(tm)
processor by providing you access to software created by Intel and its licensors and
related documentation and materials (the "Intel Software") and Intel supplied
equipment ("Intel Equipment") under the following terms and conditions:
<P>
1. To the extent that you are using software that is not supplied by Intel and/or
its licensors such as the Linux operating system and the GCC compiler, this Agreement
has no effect on your rights and your use of that software is subject to the applicable
license such as the Gnu General Public License or other applicable license.
<P>
2. This license to use the Intel Software and Intel Equipment is being provided to you
royalty-free, in consideration of your adherence to the other terms and conditions of
this license. You may distribute software that you create ("Your Software") using this
equipment through any distribution scheme you wish to use, including distributing Your
Software under an open source license agreement such as the Gnu General Public License
or distributing binaries of Your Software for a fee and subject to a different license.
<P>
3. You may modify portions of the Intel Software provided by Intel as sample source code
and incorporate such sample source or modified portions thereof into your programs and
may distribute Your Software incorporating sample source code or modifications thereof
under any license agreement of your choosing. You may not reverse engineer, decompile,
license or disassemble portions of any Intel Software provided in object code form.
<P>
4. Since the Intel Equipment that you are using is pre-release hardware and incorporates
pre-release software and is configured to permit multiple people to test code on the
equipment, this equipment will not generate reliable benchmarking data. I understand
that no reliable benchmarking data can be generated on the Intel Equipment. Therefore,
you agree that you will not disclose publicly or share with any third party any
benchmarks generated using the Intel Equpment and/or Intel Software.
<P>
5. The Intel Software provided in binary form contains confidential information of Intel
regarding technical aspects of the Itanium processor. You must use the same degree of
care to protect this confidential information of Intel that you use to protect your own
confidential information, but no less than a reasonable degree of care. You must
restrict access to the Intel Software provided in binary form to your employees who have
executed written agreements with you obligating them to protect confidential information
as required under this paragraph. The obligations of this paragraph do not apply to any
information that is or becomes published by Intel without restriction, or otherwise
becomes rightfully available to the public other than by breach of confidentiality
obligation to Intel.
<P>
6. THE SOFTWARE AND EQUIPMENT IS PROVIDED BY INTEL AND ANY EXPRESS OR IMPLIED WARRANTIES,
INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
PARTICULAR PURPOSE, AND NON-INFRINGEMENT ARE DISCLAIMED. IN NO EVENT SHALL INTEL BE LIABLE
FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING
IN ANY WAY OUT OF THE USE OF THE SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
<P>
7. You may provide to Intel additional feedback regarding the Intel Software and/or the
Intel Equipment, including suggested enhancements or modifications to the Software or
related materials. To the extent that you provide feedback to Intel, you agree that Intel
shall have no use or confidentiality restrictions on such feedback.
<P>
8. Intel retains all ownership of the Intel Software. No other rights or licenses are given
to you, except as expressly provided in this license. Upon termination, you agree to destroy
all copies of the Intel Software in your possession other than Intel Software that you
incorporated into Your Software as permitted under paragraph 2.
<P>
9. You shall comply with all US Export Regulations governing the Intel Software and Intel
Equipment. You shall not sell or otherwise transfer the Intel Software or any confidential
information of Intel to any person or any entity listed on a denial order published by the
US Government. You understand that this requirement is imposed for all transactions,
including sales, servicing, and training. You hereby certify that you are not involved in
nuclear, missile, chemical and/or biological weapons activities in violation of US Export
Regulations.
<P>
10. U.S. GOVERNMENT RESTRICTED RIGHTS: The Materials are provided with "RESTRICTED RIGHTS."
Use, duplication, or disclosure by the Government is subject to restrictions as set forth in
FAR52.227-14 and DFAR252.227-7013 et seq. or its successor. Use of the Materials by the
Government constitutes acknowledgment of Intel's proprietary rights in them.
<P>
11. USER SUBMISSIONS: Any material, information or other communication you transmit or post
to this Site will be considered non-confidential and non-proprietary ("Communications").
Intel will have no obligations with respect to the Communications. Intel and its designees
will be free to copy, disclose, distribute, incorporate and otherwise use the Communications
and all data, images, sounds, text, and other things embodied therein for any and all commercial
or non-commercial purposes. You are prohibited from posting or transmitting to or from this
Site any unlawful, threatening, libelous, defamatory, obscene, pornographic, or other material
that would violate any law.
<P>
12. USER CHAT ROOMS: Intel may, but is not obligated to, monitor or review any areas on the
Site where users transmit or post Communications or communicate solely with each other,
including but not limited to chat rooms, bulletin boards or other user forums, and the content
of any such Communications. Intel, however, will have no liability related to the content of
any such Communications, whether or not arising under the laws of copyright, libel, privacy,
obscenity, or otherwise.
<P>
13. USE OF PERSONALLY IDENTIFIABLE INFORMATION: Information submitted to Intel through forms
on the website is governed according to Intel's Electronic Personal Information Privacy Policy
(http://www.intel.com/sites/corporate/privacy.htm.)
<P>
14. LINKS TO OTHER MATERIALS: The linked sites are not under the control of Intel and Intel is
not responsible for the content of any linked site or any link contained in a linked site.
Intel reserves the right to terminate any link or linking program at any time. Intel does not
endorse companies or products to which it links and reserves the right to note as such on its
web pages. If you decide to access any of the third party sites linked to this Site, you do
this entirely at your own risk.
<P>
15. APPLICABLE LAWS: This site is controlled by Intel from its offices within the United
States of America. Intel makes no representation that Materials in the site are appropriate or
available for use in other locations, and access to them from territories where their content
is illegal is prohibited. Those who choose to access this site from other locations do so on
their own initiative and are responsible for compliance with applicable local laws. You may not
use or export the Materials in violation of U.S. export laws and regulations. Any claim relating
to the Materials shall be governed by the internal substantive laws of the State of Delaware.
<P>
16. GENERAL: Intel may revise these Terms at any time by updating this posting. You should
visit this page from time to time to review the then-current Terms because they are binding on
you. Certain provisions of these Terms may be superseded by expressly designated legal notices
or terms located on particular pages at this Site.
<P>
17. This license forms the entire agreement between you and Intel with respect to the subject
matter hereof, and may only be amended in writing by authorized representatives of both parties.
The failure of either party to enforce any rights resulting from breach will not be deemed a
waiver. This license shall be governed by, subject to, and construed according to the laws of
the United States and the State of Delaware, excluding its conflicts of laws provisions. If the
Software is used outside the United States of America, you agree that all disputes regarding
this license and the Software shall be referred to the United States District Court for Delaware
or, if there is no federal jurisdiction, to the applicable state court in Delaware.
<P>
18. You are not required to accept this agreement, since you have not signed it. However, nothing
else grants you permission to use the Intel Software or Intel Equipment. Therefore, by using the
Intel Equipment or Intel Software, you indicate your acceptance of this agreement to do so, and
all its terms and conditions for using the Intel Software and Intel Equipment.
<P>
<?php
	echo '
		<FORM ACTION="'. $PHP_SELF .'" METHOD="POST">
		<B>Enter A Brief description of the work you would like to do on the Itanium(tm) processor prototype machines:</B><BR>
		<TEXTAREA NAME="message" ROWS="20" COLS="60" WRAP="SOFT"></TEXTAREA>
		<P>
		<INPUT TYPE="SUBMIT" NAME="ACCEPT" VALUE="Yes - I Agree"> &nbsp; 
		<INPUT TYPE="SUBMIT" NAME="REJECT" VALUE="No - I Don\'t Agree">
		</FORM>';

	site_user_footer(array());

} else {

	exit_not_logged_in();

}

?>
