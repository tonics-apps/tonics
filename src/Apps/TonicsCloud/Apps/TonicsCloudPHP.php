<?php
/*
 *     Copyright (c) 2023-2024. Olayemi Faruq <olayemi@tonics.app>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Apps\TonicsCloud\Apps;

use App\Apps\TonicsCloud\Interfaces\CloudAppInterface;
use App\Apps\TonicsCloud\Interfaces\CloudAppSignalInterface;

class TonicsCloudPHP extends CloudAppInterface implements CloudAppSignalInterface
{
    private string $version = '';

    /**
     * @inheritDoc
     * @throws \Exception
     * @throws \Throwable
     */
    public function updateSettings (): void
    {
        $fpm = $this->getPostPrepareForFlight()->fpm;
        $ini = $this->getPostPrepareForFlight()->ini;

        if ($fpm) {
            $fpm = helper()->replacePlaceHolders($fpm, [
                "[[PHP_VERSION]]" => $this->phpVersion(),
            ]);
            $this->createOrReplaceFile("/etc/php/{$this->phpVersion()}/fpm/php-fpm.conf", $fpm);
        }

        if ($ini) {
            $ini = helper()->replacePlaceHolders($ini, [
                "[[PHP_VERSION]]" => $this->phpVersion(),
            ]);
            $this->createOrReplaceFile("/etc/php/{$this->phpVersion()}/fpm/php.ini", $ini);
        }
    }

    /**
     * @inheritDoc
     */
    public function install (): mixed
    {
        // TODO: Implement install() method.
    }

    /**
     * @inheritDoc
     */
    public function uninstall (): mixed
    {
        // TODO: Implement uninstall() method.
    }

    /**
     * @throws \Exception
     */
    public function prepareForFlight (array $data, string $flightType = self::PREPARATION_TYPE_SETTINGS): array
    {
        $settings = [
            'fpm' => null,
            'ini' => null,
        ];

        foreach ($data as $field) {

            if (isset($field->main_field_slug) && isset($field->field_input_name)) {

                $fieldOptions = $this->getFieldOption($field);
                $value = $fieldOptions->{$field->field_input_name} ?? null;
                if ($field->field_input_name == 'fpm') {
                    $settings['fpm'] = $value;
                }

                if ($field->field_input_name == 'ini') {
                    $settings['ini'] = $value;
                }

            }

        }

        return $settings;
    }

    /**
     *
     * Data should contain:
     *
     *  ```
     *  [
     *      'fpm' => '...' // strings
     *      'ini' => '...' // strings
     *  ]
     *  ```
     *
     * @param array $data
     *
     * @return mixed
     */
    public static function createFieldDetails (array $data = []): mixed
    {
        $fieldDetails = <<<'JSON'
[{"field_id":1,"field_parent_id":null,"field_name":"modular_rowcolumn","field_input_name":"","main_field_slug":"app-tonicscloud-app-config-php","field_options":"{\"field_slug\":\"modular_rowcolumn\",\"main_field_slug\":\"app-tonicscloud-app-config-php\",\"field_slug_unique_hash\":\"1f16y6naih8g000000000\",\"field_input_name\":\"\",\"PHP Config_cacf4c03412979c41fff\":\"on\"}"},{"field_id":2,"field_parent_id":1,"field_name":"input_text","field_input_name":"fpm","main_field_slug":"app-tonicscloud-app-config-php","field_options":"{\"field_slug\":\"input_text\",\"main_field_slug\":\"app-tonicscloud-app-config-php\",\"field_slug_unique_hash\":\"4r6t89u9fle0000000000\",\"field_input_name\":\"fpm\",\"fpm\":\";;;;;;;;;;;;;;;;;;;;;\\n; FPM Configuration ;\\n;;;;;;;;;;;;;;;;;;;;;\\n\\n; All relative paths in this configuration file are relative to PHP's install\\n; prefix (\/usr). This prefix can be dynamically changed by using the\\n; '-p' argument from the command line.\\n\\n;;;;;;;;;;;;;;;;;;\\n; Global Options ;\\n;;;;;;;;;;;;;;;;;;\\n\\n[global]\\n; Pid file\\n; Note: the default prefix is \/var\\n; Default Value: none\\n; Warning: if you change the value here, you need to modify systemd\\n; service PIDFile= setting to match the value here.\\npid = \/run\/php\/php[[PHP_VERSION]]-fpm.pid\\n\\n; Error log file\\n; If it's set to \\\"syslog\\\", log is sent to syslogd instead of being written\\n; into a local file.\\n; Note: the default prefix is \/var\\n; Default Value: log\/php-fpm.log\\nerror_log = \/var\/log\/php[[PHP_VERSION]]-fpm.log\\n\\n; syslog_facility is used to specify what type of program is logging the\\n; message. This lets syslogd specify that messages from different facilities\\n; will be handled differently.\\n; See syslog(3) for possible values (ex daemon equiv LOG_DAEMON)\\n; Default Value: daemon\\n;syslog.facility = daemon\\n\\n; syslog_ident is prepended to every message. If you have multiple FPM\\n; instances running on the same server, you can change the default value\\n; which must suit common needs.\\n; Default Value: php-fpm\\n;syslog.ident = php-fpm\\n\\n; Log level\\n; Possible Values: alert, error, warning, notice, debug\\n; Default Value: notice\\n;log_level = notice\\n\\n; Log limit on number of characters in the single line (log entry). If the\\n; line is over the limit, it is wrapped on multiple lines. The limit is for\\n; all logged characters including message prefix and suffix if present. However\\n; the new line character does not count into it as it is present only when\\n; logging to a file descriptor. It means the new line character is not present\\n; when logging to syslog.\\n; Default Value: 1024\\n;log_limit = 4096\\n\\n; Log buffering specifies if the log line is buffered which means that the\\n; line is written in a single write operation. If the value is false, then the\\n; data is written directly into the file descriptor. It is an experimental\\n; option that can potentially improve logging performance and memory usage\\n; for some heavy logging scenarios. This option is ignored if logging to syslog\\n; as it has to be always buffered.\\n; Default value: yes\\n;log_buffering = no\\n\\n; If this number of child processes exit with SIGSEGV or SIGBUS within the time\\n; interval set by emergency_restart_interval then FPM will restart. A value\\n; of '0' means 'Off'.\\n; Default Value: 0\\n;emergency_restart_threshold = 0\\n\\n; Interval of time used by emergency_restart_interval to determine when\\n; a graceful restart will be initiated.  This can be useful to work around\\n; accidental corruptions in an accelerator's shared memory.\\n; Available Units: s(econds), m(inutes), h(ours), or d(ays)\\n; Default Unit: seconds\\n; Default Value: 0\\n;emergency_restart_interval = 0\\n\\n; Time limit for child processes to wait for a reaction on signals from master.\\n; Available units: s(econds), m(inutes), h(ours), or d(ays)\\n; Default Unit: seconds\\n; Default Value: 0\\n;process_control_timeout = 0\\n\\n; The maximum number of processes FPM will fork. This has been designed to control\\n; the global number of processes when using dynamic PM within a lot of pools.\\n; Use it with caution.\\n; Note: A value of 0 indicates no limit\\n; Default Value: 0\\n; process.max = 128\\n\\n; Specify the nice(2) priority to apply to the master process (only if set)\\n; The value can vary from -19 (highest priority) to 20 (lowest priority)\\n; Note: - It will only work if the FPM master process is launched as root\\n;       - The pool process will inherit the master process priority\\n;         unless specified otherwise\\n; Default Value: no set\\n; process.priority = -19\\n\\n; Send FPM to background. Set to 'no' to keep FPM in foreground for debugging.\\n; Default Value: yes\\n;daemonize = yes\\n\\n; Set open file descriptor rlimit for the master process.\\n; Default Value: system defined value\\n;rlimit_files = 1024\\n\\n; Set max core size rlimit for the master process.\\n; Possible Values: 'unlimited' or an integer greater or equal to 0\\n; Default Value: system defined value\\n;rlimit_core = 0\\n\\n; Specify the event mechanism FPM will use. The following is available:\\n; - select     (any POSIX os)\\n; - poll       (any POSIX os)\\n; - epoll      (linux >= 2.5.44)\\n; - kqueue     (FreeBSD >= 4.1, OpenBSD >= 2.9, NetBSD >= 2.0)\\n; - \/dev\/poll  (Solaris >= 7)\\n; - port       (Solaris >= 10)\\n; Default Value: not set (auto detection)\\n;events.mechanism = epoll\\n\\n; When FPM is built with systemd integration, specify the interval,\\n; in seconds, between health report notification to systemd.\\n; Set to 0 to disable.\\n; Available Units: s(econds), m(inutes), h(ours)\\n; Default Unit: seconds\\n; Default value: 10\\n;systemd_interval = 10\\n\\n;;;;;;;;;;;;;;;;;;;;\\n; Pool Definitions ;\\n;;;;;;;;;;;;;;;;;;;;\\n\\n; Multiple pools of child processes may be started with different listening\\n; ports and different management options.  The name of the pool will be\\n; used in logs and stats. There is no limitation on the number of pools which\\n; FPM can handle. Your system will tell you anyway :)\\n\\n; Include one or more files. If glob(3) exists, it is used to include a bunch of\\n; files from a glob(3) pattern. This directive can be used everywhere in the\\n; file.\\n; Relative path can also be used. They will be prefixed by:\\n;  - the global prefix if it's been set (-p argument)\\n;  - \/usr otherwise\\ninclude=\/etc\/php\/[[PHP_VERSION]]\/fpm\/pool.d\/*.conf\"}"},{"field_id":3,"field_parent_id":1,"field_name":"input_text","field_input_name":"ini","main_field_slug":"app-tonicscloud-app-config-php","field_options":"{\"field_slug\":\"input_text\",\"main_field_slug\":\"app-tonicscloud-app-config-php\",\"field_slug_unique_hash\":\"53abszcaax40000000000\",\"field_input_name\":\"ini\",\"ini\":\"[PHP]\\n\\n;;;;;;;;;;;;;;;;;;;\\n; About php.ini   ;\\n;;;;;;;;;;;;;;;;;;;\\n; PHP's initialization file, generally called php.ini, is responsible for\\n; configuring many of the aspects of PHP's behavior.\\n\\n; PHP attempts to find and load this configuration from a number of locations.\\n; The following is a summary of its search order:\\n; 1. SAPI module specific location.\\n; 2. The PHPRC environment variable.\\n; 3. A number of predefined registry keys on Windows\\n; 4. Current working directory (except CLI)\\n; 5. The web server's directory (for SAPI modules), or directory of PHP\\n; (otherwise in Windows)\\n; 6. The directory from the --with-config-file-path compile time option, or the\\n; Windows directory (usually C:\\\\windows)\\n; See the PHP docs for more specific information.\\n; https:\/\/php.net\/configuration.file\\n\\n; The syntax of the file is extremely simple.  Whitespace and lines\\n; beginning with a semicolon are silently ignored (as you probably guessed).\\n; Section headers (e.g. [Foo]) are also silently ignored, even though\\n; they might mean something in the future.\\n\\n; Directives following the section heading [PATH=\/www\/mysite] only\\n; apply to PHP files in the \/www\/mysite directory.  Directives\\n; following the section heading [HOST=www.example.com] only apply to\\n; PHP files served from www.example.com.  Directives set in these\\n; special sections cannot be overridden by user-defined INI files or\\n; at runtime. Currently, [PATH=] and [HOST=] sections only work under\\n; CGI\/FastCGI.\\n; https:\/\/php.net\/ini.sections\\n\\n; Directives are specified using the following syntax:\\n; directive = value\\n; Directive names are *case sensitive* - foo=bar is different from FOO=bar.\\n; Directives are variables used to configure PHP or PHP extensions.\\n; There is no name validation.  If PHP can't find an expected\\n; directive because it is not set or is mistyped, a default value will be used.\\n\\n; The value can be a string, a number, a PHP constant (e.g. E_ALL or M_PI), one\\n; of the INI constants (On, Off, True, False, Yes, No and None) or an expression\\n; (e.g. E_ALL & ~E_NOTICE), a quoted string (\\\"bar\\\"), or a reference to a\\n; previously set variable or directive (e.g. ${foo})\\n\\n; Expressions in the INI file are limited to bitwise operators and parentheses:\\n; |  bitwise OR\\n; ^  bitwise XOR\\n; &  bitwise AND\\n; ~  bitwise NOT\\n; !  boolean NOT\\n\\n; Boolean flags can be turned on using the values 1, On, True or Yes.\\n; They can be turned off using the values 0, Off, False or No.\\n\\n; An empty string can be denoted by simply not writing anything after the equal\\n; sign, or by using the None keyword:\\n\\n; foo =         ; sets foo to an empty string\\n; foo = None    ; sets foo to an empty string\\n; foo = \\\"None\\\"  ; sets foo to the string 'None'\\n\\n; If you use constants in your value, and these constants belong to a\\n; dynamically loaded extension (either a PHP extension or a Zend extension),\\n; you may only use these constants *after* the line that loads the extension.\\n\\n;;;;;;;;;;;;;;;;;;;\\n; About this file ;\\n;;;;;;;;;;;;;;;;;;;\\n; PHP comes packaged with two INI files. One that is recommended to be used\\n; in production environments and one that is recommended to be used in\\n; development environments.\\n\\n; php.ini-production contains settings which hold security, performance and\\n; best practices at its core. But please be aware, these settings may break\\n; compatibility with older or less security conscience applications. We\\n; recommending using the production ini in production and testing environments.\\n\\n; php.ini-development is very similar to its production variant, except it is\\n; much more verbose when it comes to errors. We recommend using the\\n; development version only in development environments, as errors shown to\\n; application users can inadvertently leak otherwise secure information.\\n\\n; This is the php.ini-production INI file.\\n\\n;;;;;;;;;;;;;;;;;;;\\n; Quick Reference ;\\n;;;;;;;;;;;;;;;;;;;\\n\\n; The following are all the settings which are different in either the production\\n; or development versions of the INIs with respect to PHP's default behavior.\\n; Please see the actual settings later in the document for more details as to why\\n; we recommend these changes in PHP's behavior.\\n\\n; display_errors\\n;   Default Value: On\\n;   Development Value: On\\n;   Production Value: Off\\n\\n; display_startup_errors\\n;   Default Value: On\\n;   Development Value: On\\n;   Production Value: Off\\n\\n; error_reporting\\n;   Default Value: E_ALL\\n;   Development Value: E_ALL\\n;   Production Value: E_ALL & ~E_DEPRECATED & ~E_STRICT\\n\\n; log_errors\\n;   Default Value: Off\\n;   Development Value: On\\n;   Production Value: On\\n\\n; max_input_time\\n;   Default Value: -1 (Unlimited)\\n;   Development Value: 60 (60 seconds)\\n;   Production Value: 60 (60 seconds)\\n\\n; output_buffering\\n;   Default Value: Off\\n;   Development Value: 4096\\n;   Production Value: 4096\\n\\n; register_argc_argv\\n;   Default Value: On\\n;   Development Value: Off\\n;   Production Value: Off\\n\\n; request_order\\n;   Default Value: None\\n;   Development Value: \\\"GP\\\"\\n;   Production Value: \\\"GP\\\"\\n\\n; session.gc_divisor\\n;   Default Value: 100\\n;   Development Value: 1000\\n;   Production Value: 1000\\n\\n; session.sid_bits_per_character\\n;   Default Value: 4\\n;   Development Value: 5\\n;   Production Value: 5\\n\\n; short_open_tag\\n;   Default Value: On\\n;   Development Value: Off\\n;   Production Value: Off\\n\\n; variables_order\\n;   Default Value: \\\"EGPCS\\\"\\n;   Development Value: \\\"GPCS\\\"\\n;   Production Value: \\\"GPCS\\\"\\n\\n; zend.exception_ignore_args\\n;   Default Value: Off\\n;   Development Value: Off\\n;   Production Value: On\\n\\n; zend.exception_string_param_max_len\\n;   Default Value: 15\\n;   Development Value: 15\\n;   Production Value: 0\\n\\n;;;;;;;;;;;;;;;;;;;;\\n; php.ini Options  ;\\n;;;;;;;;;;;;;;;;;;;;\\n; Name for user-defined php.ini (.htaccess) files. Default is \\\".user.ini\\\"\\n;user_ini.filename = \\\".user.ini\\\"\\n\\n; To disable this feature set this option to an empty value\\n;user_ini.filename =\\n\\n; TTL for user-defined php.ini files (time-to-live) in seconds. Default is 300 seconds (5 minutes)\\n;user_ini.cache_ttl = 300\\n\\n;;;;;;;;;;;;;;;;;;;;\\n; Language Options ;\\n;;;;;;;;;;;;;;;;;;;;\\n\\n; Enable the PHP scripting language engine under Apache.\\n; https:\/\/php.net\/engine\\nengine = On\\n\\n; This directive determines whether or not PHP will recognize code between\\n; <? and ?> tags as PHP source which should be processed as such. It is\\n; generally recommended that <?php and ?> should be used and that this feature\\n; should be disabled, as enabling it may result in issues when generating XML\\n; documents, however this remains supported for backward compatibility reasons.\\n; Note that this directive does not control the <?= shorthand tag, which can be\\n; used regardless of this directive.\\n; Default Value: On\\n; Development Value: Off\\n; Production Value: Off\\n; https:\/\/php.net\/short-open-tag\\nshort_open_tag = Off\\n\\n; The number of significant digits displayed in floating point numbers.\\n; https:\/\/php.net\/precision\\nprecision = 14\\n\\n; Output buffering is a mechanism for controlling how much output data\\n; (excluding headers and cookies) PHP should keep internally before pushing that\\n; data to the client. If your application's output exceeds this setting, PHP\\n; will send that data in chunks of roughly the size you specify.\\n; Turning on this setting and managing its maximum buffer size can yield some\\n; interesting side-effects depending on your application and web server.\\n; You may be able to send headers and cookies after you've already sent output\\n; through print or echo. You also may see performance benefits if your server is\\n; emitting less packets due to buffered output versus PHP streaming the output\\n; as it gets it. On production servers, 4096 bytes is a good setting for performance\\n; reasons.\\n; Note: Output buffering can also be controlled via Output Buffering Control\\n;   functions.\\n; Possible Values:\\n;   On = Enabled and buffer is unlimited. (Use with caution)\\n;   Off = Disabled\\n;   Integer = Enables the buffer and sets its maximum size in bytes.\\n; Note: This directive is hardcoded to Off for the CLI SAPI\\n; Default Value: Off\\n; Development Value: 4096\\n; Production Value: 4096\\n; https:\/\/php.net\/output-buffering\\noutput_buffering = 4096\\n\\n; You can redirect all of the output of your scripts to a function.  For\\n; example, if you set output_handler to \\\"mb_output_handler\\\", character\\n; encoding will be transparently converted to the specified encoding.\\n; Setting any output handler automatically turns on output buffering.\\n; Note: People who wrote portable scripts should not depend on this ini\\n;   directive. Instead, explicitly set the output handler using ob_start().\\n;   Using this ini directive may cause problems unless you know what script\\n;   is doing.\\n; Note: You cannot use both \\\"mb_output_handler\\\" with \\\"ob_iconv_handler\\\"\\n;   and you cannot use both \\\"ob_gzhandler\\\" and \\\"zlib.output_compression\\\".\\n; Note: output_handler must be empty if this is set 'On' !!!!\\n;   Instead you must use zlib.output_handler.\\n; https:\/\/php.net\/output-handler\\n;output_handler =\\n\\n; URL rewriter function rewrites URL on the fly by using\\n; output buffer. You can set target tags by this configuration.\\n; \\\"form\\\" tag is special tag. It will add hidden input tag to pass values.\\n; Refer to session.trans_sid_tags for usage.\\n; Default Value: \\\"form=\\\"\\n; Development Value: \\\"form=\\\"\\n; Production Value: \\\"form=\\\"\\n;url_rewriter.tags\\n\\n; URL rewriter will not rewrite absolute URL nor form by default. To enable\\n; absolute URL rewrite, allowed hosts must be defined at RUNTIME.\\n; Refer to session.trans_sid_hosts for more details.\\n; Default Value: \\\"\\\"\\n; Development Value: \\\"\\\"\\n; Production Value: \\\"\\\"\\n;url_rewriter.hosts\\n\\n; Transparent output compression using the zlib library\\n; Valid values for this option are 'off', 'on', or a specific buffer size\\n; to be used for compression (default is 4KB)\\n; Note: Resulting chunk size may vary due to nature of compression. PHP\\n;   outputs chunks that are few hundreds bytes each as a result of\\n;   compression. If you prefer a larger chunk size for better\\n;   performance, enable output_buffering in addition.\\n; Note: You need to use zlib.output_handler instead of the standard\\n;   output_handler, or otherwise the output will be corrupted.\\n; https:\/\/php.net\/zlib.output-compression\\nzlib.output_compression = Off\\n\\n; https:\/\/php.net\/zlib.output-compression-level\\n;zlib.output_compression_level = -1\\n\\n; You cannot specify additional output handlers if zlib.output_compression\\n; is activated here. This setting does the same as output_handler but in\\n; a different order.\\n; https:\/\/php.net\/zlib.output-handler\\n;zlib.output_handler =\\n\\n; Implicit flush tells PHP to tell the output layer to flush itself\\n; automatically after every output block.  This is equivalent to calling the\\n; PHP function flush() after each and every call to print() or echo() and each\\n; and every HTML block.  Turning this option on has serious performance\\n; implications and is generally recommended for debugging purposes only.\\n; https:\/\/php.net\/implicit-flush\\n; Note: This directive is hardcoded to On for the CLI SAPI\\nimplicit_flush = Off\\n\\n; The unserialize callback function will be called (with the undefined class'\\n; name as parameter), if the unserializer finds an undefined class\\n; which should be instantiated. A warning appears if the specified function is\\n; not defined, or if the function doesn't include\/implement the missing class.\\n; So only set this entry, if you really want to implement such a\\n; callback-function.\\nunserialize_callback_func =\\n\\n; The unserialize_max_depth specifies the default depth limit for unserialized\\n; structures. Setting the depth limit too high may result in stack overflows\\n; during unserialization. The unserialize_max_depth ini setting can be\\n; overridden by the max_depth option on individual unserialize() calls.\\n; A value of 0 disables the depth limit.\\n;unserialize_max_depth = 4096\\n\\n; When floats & doubles are serialized, store serialize_precision significant\\n; digits after the floating point. The default value ensures that when floats\\n; are decoded with unserialize, the data will remain the same.\\n; The value is also used for json_encode when encoding double values.\\n; If -1 is used, then dtoa mode 0 is used which automatically select the best\\n; precision.\\nserialize_precision = -1\\n\\n; open_basedir, if set, limits all file operations to the defined directory\\n; and below.  This directive makes most sense if used in a per-directory\\n; or per-virtualhost web server configuration file.\\n; Note: disables the realpath cache\\n; https:\/\/php.net\/open-basedir\\n;open_basedir =\\n\\n; This directive allows you to disable certain functions.\\n; It receives a comma-delimited list of function names.\\n; https:\/\/php.net\/disable-functions\\ndisable_functions =\\n\\n; This directive allows you to disable certain classes.\\n; It receives a comma-delimited list of class names.\\n; https:\/\/php.net\/disable-classes\\ndisable_classes =\\n\\n; Colors for Syntax Highlighting mode.  Anything that's acceptable in\\n; <span style=\\\"color: ???????\\\"> would work.\\n; https:\/\/php.net\/syntax-highlighting\\n;highlight.string  = #DD0000\\n;highlight.comment = #FF9900\\n;highlight.keyword = #007700\\n;highlight.default = #0000BB\\n;highlight.html    = #000000\\n\\n; If enabled, the request will be allowed to complete even if the user aborts\\n; the request. Consider enabling it if executing long requests, which may end up\\n; being interrupted by the user or a browser timing out. PHP's default behavior\\n; is to disable this feature.\\n; https:\/\/php.net\/ignore-user-abort\\n;ignore_user_abort = On\\n\\n; Determines the size of the realpath cache to be used by PHP. This value should\\n; be increased on systems where PHP opens many files to reflect the quantity of\\n; the file operations performed.\\n; Note: if open_basedir is set, the cache is disabled\\n; https:\/\/php.net\/realpath-cache-size\\n;realpath_cache_size = 4096k\\n\\n; Duration of time, in seconds for which to cache realpath information for a given\\n; file or directory. For systems with rarely changing files, consider increasing this\\n; value.\\n; https:\/\/php.net\/realpath-cache-ttl\\n;realpath_cache_ttl = 120\\n\\n; Enables or disables the circular reference collector.\\n; https:\/\/php.net\/zend.enable-gc\\nzend.enable_gc = On\\n\\n; If enabled, scripts may be written in encodings that are incompatible with\\n; the scanner.  CP936, Big5, CP949 and Shift_JIS are the examples of such\\n; encodings.  To use this feature, mbstring extension must be enabled.\\n;zend.multibyte = Off\\n\\n; Allows to set the default encoding for the scripts.  This value will be used\\n; unless \\\"declare(encoding=...)\\\" directive appears at the top of the script.\\n; Only affects if zend.multibyte is set.\\n;zend.script_encoding =\\n\\n; Allows to include or exclude arguments from stack traces generated for exceptions.\\n; In production, it is recommended to turn this setting on to prohibit the output\\n; of sensitive information in stack traces\\n; Default Value: Off\\n; Development Value: Off\\n; Production Value: On\\nzend.exception_ignore_args = On\\n\\n; Allows setting the maximum string length in an argument of a stringified stack trace\\n; to a value between 0 and 1000000.\\n; This has no effect when zend.exception_ignore_args is enabled.\\n; Default Value: 15\\n; Development Value: 15\\n; Production Value: 0\\n; In production, it is recommended to set this to 0 to reduce the output\\n; of sensitive information in stack traces.\\nzend.exception_string_param_max_len = 0\\n\\n;;;;;;;;;;;;;;;;;\\n; Miscellaneous ;\\n;;;;;;;;;;;;;;;;;\\n\\n; Decides whether PHP may expose the fact that it is installed on the server\\n; (e.g. by adding its signature to the Web server header).  It is no security\\n; threat in any way, but it makes it possible to determine whether you use PHP\\n; on your server or not.\\n; https:\/\/php.net\/expose-php\\nexpose_php = Off\\n\\n;;;;;;;;;;;;;;;;;;;\\n; Resource Limits ;\\n;;;;;;;;;;;;;;;;;;;\\n\\n; Maximum execution time of each script, in seconds\\n; https:\/\/php.net\/max-execution-time\\n; Note: This directive is hardcoded to 0 for the CLI SAPI\\nmax_execution_time = 30\\n\\n; Maximum amount of time each script may spend parsing request data. It's a good\\n; idea to limit this time on productions servers in order to eliminate unexpectedly\\n; long running scripts.\\n; Note: This directive is hardcoded to -1 for the CLI SAPI\\n; Default Value: -1 (Unlimited)\\n; Development Value: 60 (60 seconds)\\n; Production Value: 60 (60 seconds)\\n; https:\/\/php.net\/max-input-time\\nmax_input_time = 60\\n\\n; Maximum input variable nesting level\\n; https:\/\/php.net\/max-input-nesting-level\\n;max_input_nesting_level = 64\\n\\n; How many GET\/POST\/COOKIE input variables may be accepted\\n;max_input_vars = 1000\\n\\n; How many multipart body parts (combined input variable and file uploads) may\\n; be accepted.\\n; Default Value: -1 (Sum of max_input_vars and max_file_uploads)\\n;max_multipart_body_parts = 1500\\n\\n; Maximum amount of memory a script may consume\\n; https:\/\/php.net\/memory-limit\\nmemory_limit = 500M\\n\\n;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;\\n; Error handling and logging ;\\n;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;\\n\\n; This directive informs PHP of which errors, warnings and notices you would like\\n; it to take action for. The recommended way of setting values for this\\n; directive is through the use of the error level constants and bitwise\\n; operators. The error level constants are below here for convenience as well as\\n; some common settings and their meanings.\\n; By default, PHP is set to take action on all errors, notices and warnings EXCEPT\\n; those related to E_NOTICE and E_STRICT, which together cover best practices and\\n; recommended coding standards in PHP. For performance reasons, this is the\\n; recommend error reporting setting. Your production server shouldn't be wasting\\n; resources complaining about best practices and coding standards. That's what\\n; development servers and development settings are for.\\n; Note: The php.ini-development file has this setting as E_ALL. This\\n; means it pretty much reports everything which is exactly what you want during\\n; development and early testing.\\n;\\n; Error Level Constants:\\n; E_ALL             - All errors and warnings\\n; E_ERROR           - fatal run-time errors\\n; E_RECOVERABLE_ERROR  - almost fatal run-time errors\\n; E_WARNING         - run-time warnings (non-fatal errors)\\n; E_PARSE           - compile-time parse errors\\n; E_NOTICE          - run-time notices (these are warnings which often result\\n;                     from a bug in your code, but it's possible that it was\\n;                     intentional (e.g., using an uninitialized variable and\\n;                     relying on the fact it is automatically initialized to an\\n;                     empty string)\\n; E_STRICT          - run-time notices, enable to have PHP suggest changes\\n;                     to your code which will ensure the best interoperability\\n;                     and forward compatibility of your code\\n; E_CORE_ERROR      - fatal errors that occur during PHP's initial startup\\n; E_CORE_WARNING    - warnings (non-fatal errors) that occur during PHP's\\n;                     initial startup\\n; E_COMPILE_ERROR   - fatal compile-time errors\\n; E_COMPILE_WARNING - compile-time warnings (non-fatal errors)\\n; E_USER_ERROR      - user-generated error message\\n; E_USER_WARNING    - user-generated warning message\\n; E_USER_NOTICE     - user-generated notice message\\n; E_DEPRECATED      - warn about code that will not work in future versions\\n;                     of PHP\\n; E_USER_DEPRECATED - user-generated deprecation warnings\\n;\\n; Common Values:\\n;   E_ALL (Show all errors, warnings and notices including coding standards.)\\n;   E_ALL & ~E_NOTICE  (Show all errors, except for notices)\\n;   E_ALL & ~E_NOTICE & ~E_STRICT  (Show all errors, except for notices and coding standards warnings.)\\n;   E_COMPILE_ERROR|E_RECOVERABLE_ERROR|E_ERROR|E_CORE_ERROR  (Show only errors)\\n; Default Value: E_ALL\\n; Development Value: E_ALL\\n; Production Value: E_ALL & ~E_DEPRECATED & ~E_STRICT\\n; https:\/\/php.net\/error-reporting\\nerror_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT\\n\\n; This directive controls whether or not and where PHP will output errors,\\n; notices and warnings too. Error output is very useful during development, but\\n; it could be very dangerous in production environments. Depending on the code\\n; which is triggering the error, sensitive information could potentially leak\\n; out of your application such as database usernames and passwords or worse.\\n; For production environments, we recommend logging errors rather than\\n; sending them to STDOUT.\\n; Possible Values:\\n;   Off = Do not display any errors\\n;   stderr = Display errors to STDERR (affects only CGI\/CLI binaries!)\\n;   On or stdout = Display errors to STDOUT\\n; Default Value: On\\n; Development Value: On\\n; Production Value: Off\\n; https:\/\/php.net\/display-errors\\ndisplay_errors = Off\\n\\n; The display of errors which occur during PHP's startup sequence are handled\\n; separately from display_errors. We strongly recommend you set this to 'off'\\n; for production servers to avoid leaking configuration details.\\n; Default Value: On\\n; Development Value: On\\n; Production Value: Off\\n; https:\/\/php.net\/display-startup-errors\\ndisplay_startup_errors = Off\\n\\n; Besides displaying errors, PHP can also log errors to locations such as a\\n; server-specific log, STDERR, or a location specified by the error_log\\n; directive found below. While errors should not be displayed on productions\\n; servers they should still be monitored and logging is a great way to do that.\\n; Default Value: Off\\n; Development Value: On\\n; Production Value: On\\n; https:\/\/php.net\/log-errors\\nlog_errors = On\\n\\n; Do not log repeated messages. Repeated errors must occur in same file on same\\n; line unless ignore_repeated_source is set true.\\n; https:\/\/php.net\/ignore-repeated-errors\\nignore_repeated_errors = Off\\n\\n; Ignore source of message when ignoring repeated messages. When this setting\\n; is On you will not log errors with repeated messages from different files or\\n; source lines.\\n; https:\/\/php.net\/ignore-repeated-source\\nignore_repeated_source = Off\\n\\n; If this parameter is set to Off, then memory leaks will not be shown (on\\n; stdout or in the log). This is only effective in a debug compile, and if\\n; error reporting includes E_WARNING in the allowed list\\n; https:\/\/php.net\/report-memleaks\\nreport_memleaks = On\\n\\n; This setting is off by default.\\n;report_zend_debug = 0\\n\\n; Turn off normal error reporting and emit XML-RPC error XML\\n; https:\/\/php.net\/xmlrpc-errors\\n;xmlrpc_errors = 0\\n\\n; An XML-RPC faultCode\\n;xmlrpc_error_number = 0\\n\\n; When PHP displays or logs an error, it has the capability of formatting the\\n; error message as HTML for easier reading. This directive controls whether\\n; the error message is formatted as HTML or not.\\n; Note: This directive is hardcoded to Off for the CLI SAPI\\n; https:\/\/php.net\/html-errors\\n;html_errors = On\\n\\n; If html_errors is set to On *and* docref_root is not empty, then PHP\\n; produces clickable error messages that direct to a page describing the error\\n; or function causing the error in detail.\\n; You can download a copy of the PHP manual from https:\/\/php.net\/docs\\n; and change docref_root to the base URL of your local copy including the\\n; leading '\/'. You must also specify the file extension being used including\\n; the dot. PHP's default behavior is to leave these settings empty, in which\\n; case no links to documentation are generated.\\n; Note: Never use this feature for production boxes.\\n; https:\/\/php.net\/docref-root\\n; Examples\\n;docref_root = \\\"\/phpmanual\/\\\"\\n\\n; https:\/\/php.net\/docref-ext\\n;docref_ext = .html\\n\\n; String to output before an error message. PHP's default behavior is to leave\\n; this setting blank.\\n; https:\/\/php.net\/error-prepend-string\\n; Example:\\n;error_prepend_string = \\\"<span style='color: #ff0000'>\\\"\\n\\n; String to output after an error message. PHP's default behavior is to leave\\n; this setting blank.\\n; https:\/\/php.net\/error-append-string\\n; Example:\\n;error_append_string = \\\"<\/span>\\\"\\n\\n; Log errors to specified file. PHP's default behavior is to leave this value\\n; empty.\\n; https:\/\/php.net\/error-log\\n; Example:\\n;error_log = php_errors.log\\n; Log errors to syslog (Event Log on Windows).\\n;error_log = syslog\\n\\n; The syslog ident is a string which is prepended to every message logged\\n; to syslog. Only used when error_log is set to syslog.\\n;syslog.ident = php\\n\\n; The syslog facility is used to specify what type of program is logging\\n; the message. Only used when error_log is set to syslog.\\n;syslog.facility = user\\n\\n; Set this to disable filtering control characters (the default).\\n; Some loggers only accept NVT-ASCII, others accept anything that's not\\n; control characters. If your logger accepts everything, then no filtering\\n; is needed at all.\\n; Allowed values are:\\n;   ascii (all printable ASCII characters and NL)\\n;   no-ctrl (all characters except control characters)\\n;   all (all characters)\\n;   raw (like \\\"all\\\", but messages are not split at newlines)\\n; https:\/\/php.net\/syslog.filter\\n;syslog.filter = ascii\\n\\n;windows.show_crt_warning\\n; Default value: 0\\n; Development value: 0\\n; Production value: 0\\n\\n;;;;;;;;;;;;;;;;;\\n; Data Handling ;\\n;;;;;;;;;;;;;;;;;\\n\\n; The separator used in PHP generated URLs to separate arguments.\\n; PHP's default setting is \\\"&\\\".\\n; https:\/\/php.net\/arg-separator.output\\n; Example:\\n;arg_separator.output = \\\"&amp;\\\"\\n\\n; List of separator(s) used by PHP to parse input URLs into variables.\\n; PHP's default setting is \\\"&\\\".\\n; NOTE: Every character in this directive is considered as separator!\\n; https:\/\/php.net\/arg-separator.input\\n; Example:\\n;arg_separator.input = \\\";&\\\"\\n\\n; This directive determines which super global arrays are registered when PHP\\n; starts up. G,P,C,E & S are abbreviations for the following respective super\\n; globals: GET, POST, COOKIE, ENV and SERVER. There is a performance penalty\\n; paid for the registration of these arrays and because ENV is not as commonly\\n; used as the others, ENV is not recommended on productions servers. You\\n; can still get access to the environment variables through getenv() should you\\n; need to.\\n; Default Value: \\\"EGPCS\\\"\\n; Development Value: \\\"GPCS\\\"\\n; Production Value: \\\"GPCS\\\";\\n; https:\/\/php.net\/variables-order\\nvariables_order = \\\"GPCS\\\"\\n\\n; This directive determines which super global data (G,P & C) should be\\n; registered into the super global array REQUEST. If so, it also determines\\n; the order in which that data is registered. The values for this directive\\n; are specified in the same manner as the variables_order directive,\\n; EXCEPT one. Leaving this value empty will cause PHP to use the value set\\n; in the variables_order directive. It does not mean it will leave the super\\n; globals array REQUEST empty.\\n; Default Value: None\\n; Development Value: \\\"GP\\\"\\n; Production Value: \\\"GP\\\"\\n; https:\/\/php.net\/request-order\\nrequest_order = \\\"GP\\\"\\n\\n; This directive determines whether PHP registers $argv & $argc each time it\\n; runs. $argv contains an array of all the arguments passed to PHP when a script\\n; is invoked. $argc contains an integer representing the number of arguments\\n; that were passed when the script was invoked. These arrays are extremely\\n; useful when running scripts from the command line. When this directive is\\n; enabled, registering these variables consumes CPU cycles and memory each time\\n; a script is executed. For performance reasons, this feature should be disabled\\n; on production servers.\\n; Note: This directive is hardcoded to On for the CLI SAPI\\n; Default Value: On\\n; Development Value: Off\\n; Production Value: Off\\n; https:\/\/php.net\/register-argc-argv\\nregister_argc_argv = Off\\n\\n; When enabled, the ENV, REQUEST and SERVER variables are created when they're\\n; first used (Just In Time) instead of when the script starts. If these\\n; variables are not used within a script, having this directive on will result\\n; in a performance gain. The PHP directive register_argc_argv must be disabled\\n; for this directive to have any effect.\\n; https:\/\/php.net\/auto-globals-jit\\nauto_globals_jit = On\\n\\n; Whether PHP will read the POST data.\\n; This option is enabled by default.\\n; Most likely, you won't want to disable this option globally. It causes $_POST\\n; and $_FILES to always be empty; the only way you will be able to read the\\n; POST data will be through the php:\/\/input stream wrapper. This can be useful\\n; to proxy requests or to process the POST data in a memory efficient fashion.\\n; https:\/\/php.net\/enable-post-data-reading\\n;enable_post_data_reading = Off\\n\\n; Maximum size of POST data that PHP will accept.\\n; Its value may be 0 to disable the limit. It is ignored if POST data reading\\n; is disabled through enable_post_data_reading.\\n; https:\/\/php.net\/post-max-size\\npost_max_size = 8M\\n\\n; Automatically add files before PHP document.\\n; https:\/\/php.net\/auto-prepend-file\\nauto_prepend_file =\\n\\n; Automatically add files after PHP document.\\n; https:\/\/php.net\/auto-append-file\\nauto_append_file =\\n\\n; By default, PHP will output a media type using the Content-Type header. To\\n; disable this, simply set it to be empty.\\n;\\n; PHP's built-in default media type is set to text\/html.\\n; https:\/\/php.net\/default-mimetype\\ndefault_mimetype = \\\"text\/html\\\"\\n\\n; PHP's default character set is set to UTF-8.\\n; https:\/\/php.net\/default-charset\\ndefault_charset = \\\"UTF-8\\\"\\n\\n; PHP internal character encoding is set to empty.\\n; If empty, default_charset is used.\\n; https:\/\/php.net\/internal-encoding\\n;internal_encoding =\\n\\n; PHP input character encoding is set to empty.\\n; If empty, default_charset is used.\\n; https:\/\/php.net\/input-encoding\\n;input_encoding =\\n\\n; PHP output character encoding is set to empty.\\n; If empty, default_charset is used.\\n; See also output_buffer.\\n; https:\/\/php.net\/output-encoding\\n;output_encoding =\\n\\n;;;;;;;;;;;;;;;;;;;;;;;;;\\n; Paths and Directories ;\\n;;;;;;;;;;;;;;;;;;;;;;;;;\\n\\n; UNIX: \\\"\/path1:\/path2\\\"\\n;include_path = \\\".:\/usr\/share\/php\\\"\\n;\\n; Windows: \\\"\\\\path1;\\\\path2\\\"\\n;include_path = \\\".;c:\\\\php\\\\includes\\\"\\n;\\n; PHP's default setting for include_path is \\\".;\/path\/to\/php\/pear\\\"\\n; https:\/\/php.net\/include-path\\n\\n; The root of the PHP pages, used only if nonempty.\\n; if PHP was not compiled with FORCE_REDIRECT, you SHOULD set doc_root\\n; if you are running php as a CGI under any web server (other than IIS)\\n; see documentation for security issues.  The alternate is to use the\\n; cgi.force_redirect configuration below\\n; https:\/\/php.net\/doc-root\\ndoc_root =\\n\\n; The directory under which PHP opens the script using \/~username used only\\n; if nonempty.\\n; https:\/\/php.net\/user-dir\\nuser_dir =\\n\\n; Directory in which the loadable extensions (modules) reside.\\n; https:\/\/php.net\/extension-dir\\n;extension_dir = \\\".\/\\\"\\n; On windows:\\n;extension_dir = \\\"ext\\\"\\n\\n; Directory where the temporary files should be placed.\\n; Defaults to the system default (see sys_get_temp_dir)\\n;sys_temp_dir = \\\"\/tmp\\\"\\n\\n; Whether or not to enable the dl() function.  The dl() function does NOT work\\n; properly in multithreaded servers, such as IIS or Zeus, and is automatically\\n; disabled on them.\\n; https:\/\/php.net\/enable-dl\\nenable_dl = Off\\n\\n; cgi.force_redirect is necessary to provide security running PHP as a CGI under\\n; most web servers.  Left undefined, PHP turns this on by default.  You can\\n; turn it off here AT YOUR OWN RISK\\n; **You CAN safely turn this off for IIS, in fact, you MUST.**\\n; https:\/\/php.net\/cgi.force-redirect\\n;cgi.force_redirect = 1\\n\\n; if cgi.nph is enabled it will force cgi to always sent Status: 200 with\\n; every request. PHP's default behavior is to disable this feature.\\n;cgi.nph = 1\\n\\n; if cgi.force_redirect is turned on, and you are not running under Apache or Netscape\\n; (iPlanet) web servers, you MAY need to set an environment variable name that PHP\\n; will look for to know it is OK to continue execution.  Setting this variable MAY\\n; cause security issues, KNOW WHAT YOU ARE DOING FIRST.\\n; https:\/\/php.net\/cgi.redirect-status-env\\n;cgi.redirect_status_env =\\n\\n; cgi.fix_pathinfo provides *real* PATH_INFO\/PATH_TRANSLATED support for CGI.  PHP's\\n; previous behaviour was to set PATH_TRANSLATED to SCRIPT_FILENAME, and to not grok\\n; what PATH_INFO is.  For more information on PATH_INFO, see the cgi specs.  Setting\\n; this to 1 will cause PHP CGI to fix its paths to conform to the spec.  A setting\\n; of zero causes PHP to behave as before.  Default is 1.  You should fix your scripts\\n; to use SCRIPT_FILENAME rather than PATH_TRANSLATED.\\n; https:\/\/php.net\/cgi.fix-pathinfo\\n;cgi.fix_pathinfo=1\\n\\n; if cgi.discard_path is enabled, the PHP CGI binary can safely be placed outside\\n; of the web tree and people will not be able to circumvent .htaccess security.\\n;cgi.discard_path=1\\n\\n; FastCGI under IIS supports the ability to impersonate\\n; security tokens of the calling client.  This allows IIS to define the\\n; security context that the request runs under.  mod_fastcgi under Apache\\n; does not currently support this feature (03\/17\/2002)\\n; Set to 1 if running under IIS.  Default is zero.\\n; https:\/\/php.net\/fastcgi.impersonate\\n;fastcgi.impersonate = 1\\n\\n; Disable logging through FastCGI connection. PHP's default behavior is to enable\\n; this feature.\\n;fastcgi.logging = 0\\n\\n; cgi.rfc2616_headers configuration option tells PHP what type of headers to\\n; use when sending HTTP response code. If set to 0, PHP sends Status: header that\\n; is supported by Apache. When this option is set to 1, PHP will send\\n; RFC2616 compliant header.\\n; Default is zero.\\n; https:\/\/php.net\/cgi.rfc2616-headers\\n;cgi.rfc2616_headers = 0\\n\\n; cgi.check_shebang_line controls whether CGI PHP checks for line starting with #!\\n; (shebang) at the top of the running script. This line might be needed if the\\n; script support running both as stand-alone script and via PHP CGI<. PHP in CGI\\n; mode skips this line and ignores its content if this directive is turned on.\\n; https:\/\/php.net\/cgi.check-shebang-line\\n;cgi.check_shebang_line=1\\n\\n;;;;;;;;;;;;;;;;\\n; File Uploads ;\\n;;;;;;;;;;;;;;;;\\n\\n; Whether to allow HTTP file uploads.\\n; https:\/\/php.net\/file-uploads\\nfile_uploads = On\\n\\n; Temporary directory for HTTP uploaded files (will use system default if not\\n; specified).\\n; https:\/\/php.net\/upload-tmp-dir\\n;upload_tmp_dir =\\n\\n; Maximum allowed size for uploaded files.\\n; https:\/\/php.net\/upload-max-filesize\\nupload_max_filesize = 2M\\n\\n; Maximum number of files that can be uploaded via a single request\\nmax_file_uploads = 20\\n\\n;;;;;;;;;;;;;;;;;;\\n; Fopen wrappers ;\\n;;;;;;;;;;;;;;;;;;\\n\\n; Whether to allow the treatment of URLs (like http:\/\/ or ftp:\/\/) as files.\\n; https:\/\/php.net\/allow-url-fopen\\nallow_url_fopen = On\\n\\n; Whether to allow include\/require to open URLs (like https:\/\/ or ftp:\/\/) as files.\\n; https:\/\/php.net\/allow-url-include\\nallow_url_include = Off\\n\\n; Define the anonymous ftp password (your email address). PHP's default setting\\n; for this is empty.\\n; https:\/\/php.net\/from\\n;from=\\\"john@doe.com\\\"\\n\\n; Define the User-Agent string. PHP's default setting for this is empty.\\n; https:\/\/php.net\/user-agent\\n;user_agent=\\\"PHP\\\"\\n\\n; Default timeout for socket based streams (seconds)\\n; https:\/\/php.net\/default-socket-timeout\\ndefault_socket_timeout = 60\\n\\n; If your scripts have to deal with files from Macintosh systems,\\n; or you are running on a Mac and need to deal with files from\\n; unix or win32 systems, setting this flag will cause PHP to\\n; automatically detect the EOL character in those files so that\\n; fgets() and file() will work regardless of the source of the file.\\n; https:\/\/php.net\/auto-detect-line-endings\\n;auto_detect_line_endings = Off\\n\\n;;;;;;;;;;;;;;;;;;;;;;\\n; Dynamic Extensions ;\\n;;;;;;;;;;;;;;;;;;;;;;\\n\\n; If you wish to have an extension loaded automatically, use the following\\n; syntax:\\n;\\n;   extension=modulename\\n;\\n; For example:\\n;\\n;   extension=mysqli\\n;\\n; When the extension library to load is not located in the default extension\\n; directory, You may specify an absolute path to the library file:\\n;\\n;   extension=\/path\/to\/extension\/mysqli.so\\n;\\n; Note : The syntax used in previous PHP versions ('extension=<ext>.so' and\\n; 'extension='php_<ext>.dll') is supported for legacy reasons and may be\\n; deprecated in a future PHP major version. So, when it is possible, please\\n; move to the new ('extension=<ext>) syntax.\\n;\\n; Notes for Windows environments :\\n;\\n; - Many DLL files are located in the ext\/\\n;   extension folders as well as the separate PECL DLL download.\\n;   Be sure to appropriately set the extension_dir directive.\\n;\\n;extension=bz2\\n\\n; The ldap extension must be before curl if OpenSSL 1.0.2 and OpenLDAP is used\\n; otherwise it results in segfault when unloading after using SASL.\\n; See https:\/\/github.com\/php\/php-src\/issues\/8620 for more info.\\n;extension=ldap\\n\\n;extension=curl\\n;extension=ffi\\n;extension=ftp\\n;extension=fileinfo\\n;extension=gd\\n;extension=gettext\\n;extension=gmp\\n;extension=intl\\n;extension=imap\\n;extension=mbstring\\n;extension=exif      ; Must be after mbstring as it depends on it\\n;extension=mysqli\\n;extension=oci8_12c  ; Use with Oracle Database 12c Instant Client\\n;extension=oci8_19  ; Use with Oracle Database 19 Instant Client\\n;extension=odbc\\n;extension=openssl\\n;extension=pdo_firebird\\n;extension=pdo_mysql\\n;extension=pdo_oci\\n;extension=pdo_odbc\\n;extension=pdo_pgsql\\n;extension=pdo_sqlite\\n;extension=pgsql\\n;extension=shmop\\n\\n; The MIBS data available in the PHP distribution must be installed.\\n; See https:\/\/www.php.net\/manual\/en\/snmp.installation.php\\n;extension=snmp\\n\\n;extension=soap\\n;extension=sockets\\n;extension=sodium\\n;extension=sqlite3\\n;extension=tidy\\n;extension=xsl\\n;extension=zip\\n\\n;zend_extension=opcache\\n\\n;;;;;;;;;;;;;;;;;;;\\n; Module Settings ;\\n;;;;;;;;;;;;;;;;;;;\\n\\n[CLI Server]\\n; Whether the CLI web server uses ANSI color coding in its terminal output.\\ncli_server.color = On\\n\\n[Date]\\n; Defines the default timezone used by the date functions\\n; https:\/\/php.net\/date.timezone\\n;date.timezone =\\n\\n; https:\/\/php.net\/date.default-latitude\\n;date.default_latitude = 31.7667\\n\\n; https:\/\/php.net\/date.default-longitude\\n;date.default_longitude = 35.2333\\n\\n; https:\/\/php.net\/date.sunrise-zenith\\n;date.sunrise_zenith = 90.833333\\n\\n; https:\/\/php.net\/date.sunset-zenith\\n;date.sunset_zenith = 90.833333\\n\\n[filter]\\n; https:\/\/php.net\/filter.default\\n;filter.default = unsafe_raw\\n\\n; https:\/\/php.net\/filter.default-flags\\n;filter.default_flags =\\n\\n[iconv]\\n; Use of this INI entry is deprecated, use global input_encoding instead.\\n; If empty, default_charset or input_encoding or iconv.input_encoding is used.\\n; The precedence is: default_charset < input_encoding < iconv.input_encoding\\n;iconv.input_encoding =\\n\\n; Use of this INI entry is deprecated, use global internal_encoding instead.\\n; If empty, default_charset or internal_encoding or iconv.internal_encoding is used.\\n; The precedence is: default_charset < internal_encoding < iconv.internal_encoding\\n;iconv.internal_encoding =\\n\\n; Use of this INI entry is deprecated, use global output_encoding instead.\\n; If empty, default_charset or output_encoding or iconv.output_encoding is used.\\n; The precedence is: default_charset < output_encoding < iconv.output_encoding\\n; To use an output encoding conversion, iconv's output handler must be set\\n; otherwise output encoding conversion cannot be performed.\\n;iconv.output_encoding =\\n\\n[imap]\\n; rsh\/ssh logins are disabled by default. Use this INI entry if you want to\\n; enable them. Note that the IMAP library does not filter mailbox names before\\n; passing them to rsh\/ssh command, thus passing untrusted data to this function\\n; with rsh\/ssh enabled is insecure.\\n;imap.enable_insecure_rsh=0\\n\\n[intl]\\n;intl.default_locale =\\n; This directive allows you to produce PHP errors when some error\\n; happens within intl functions. The value is the level of the error produced.\\n; Default is 0, which does not produce any errors.\\n;intl.error_level = E_WARNING\\n;intl.use_exceptions = 0\\n\\n[sqlite3]\\n; Directory pointing to SQLite3 extensions\\n; https:\/\/php.net\/sqlite3.extension-dir\\n;sqlite3.extension_dir =\\n\\n; SQLite defensive mode flag (only available from SQLite 3.26+)\\n; When the defensive flag is enabled, language features that allow ordinary\\n; SQL to deliberately corrupt the database file are disabled. This forbids\\n; writing directly to the schema, shadow tables (eg. FTS data tables), or\\n; the sqlite_dbpage virtual table.\\n; https:\/\/www.sqlite.org\/c3ref\/c_dbconfig_defensive.html\\n; (for older SQLite versions, this flag has no use)\\n;sqlite3.defensive = 1\\n\\n[Pcre]\\n; PCRE library backtracking limit.\\n; https:\/\/php.net\/pcre.backtrack-limit\\n;pcre.backtrack_limit=100000\\n\\n; PCRE library recursion limit.\\n; Please note that if you set this value to a high number you may consume all\\n; the available process stack and eventually crash PHP (due to reaching the\\n; stack size limit imposed by the Operating System).\\n; https:\/\/php.net\/pcre.recursion-limit\\n;pcre.recursion_limit=100000\\n\\n; Enables or disables JIT compilation of patterns. This requires the PCRE\\n; library to be compiled with JIT support.\\n;pcre.jit=1\\n\\n[Pdo]\\n; Whether to pool ODBC connections. Can be one of \\\"strict\\\", \\\"relaxed\\\" or \\\"off\\\"\\n; https:\/\/php.net\/pdo-odbc.connection-pooling\\n;pdo_odbc.connection_pooling=strict\\n\\n[Pdo_mysql]\\n; Default socket name for local MySQL connects.  If empty, uses the built-in\\n; MySQL defaults.\\npdo_mysql.default_socket=\\n\\n[Phar]\\n; https:\/\/php.net\/phar.readonly\\n;phar.readonly = On\\n\\n; https:\/\/php.net\/phar.require-hash\\n;phar.require_hash = On\\n\\n;phar.cache_list =\\n\\n[mail function]\\n; For Win32 only.\\n; https:\/\/php.net\/smtp\\nSMTP = localhost\\n; https:\/\/php.net\/smtp-port\\nsmtp_port = 25\\n\\n; For Win32 only.\\n; https:\/\/php.net\/sendmail-from\\n;sendmail_from = me@example.com\\n\\n; For Unix only.  You may supply arguments as well (default: \\\"sendmail -t -i\\\").\\n; https:\/\/php.net\/sendmail-path\\n;sendmail_path =\\n\\n; Force the addition of the specified parameters to be passed as extra parameters\\n; to the sendmail binary. These parameters will always replace the value of\\n; the 5th parameter to mail().\\n;mail.force_extra_parameters =\\n\\n; Add X-PHP-Originating-Script: that will include uid of the script followed by the filename\\nmail.add_x_header = Off\\n\\n; Use mixed LF and CRLF line separators to keep compatibility with some\\n; RFC 2822 non conformant MTA.\\nmail.mixed_lf_and_crlf = Off\\n\\n; The path to a log file that will log all mail() calls. Log entries include\\n; the full path of the script, line number, To address and headers.\\n;mail.log =\\n; Log mail to syslog (Event Log on Windows).\\n;mail.log = syslog\\n\\n[ODBC]\\n; https:\/\/php.net\/odbc.default-db\\n;odbc.default_db    =  Not yet implemented\\n\\n; https:\/\/php.net\/odbc.default-user\\n;odbc.default_user  =  Not yet implemented\\n\\n; https:\/\/php.net\/odbc.default-pw\\n;odbc.default_pw    =  Not yet implemented\\n\\n; Controls the ODBC cursor model.\\n; Default: SQL_CURSOR_STATIC (default).\\n;odbc.default_cursortype\\n\\n; Allow or prevent persistent links.\\n; https:\/\/php.net\/odbc.allow-persistent\\nodbc.allow_persistent = On\\n\\n; Check that a connection is still valid before reuse.\\n; https:\/\/php.net\/odbc.check-persistent\\nodbc.check_persistent = On\\n\\n; Maximum number of persistent links.  -1 means no limit.\\n; https:\/\/php.net\/odbc.max-persistent\\nodbc.max_persistent = -1\\n\\n; Maximum number of links (persistent + non-persistent).  -1 means no limit.\\n; https:\/\/php.net\/odbc.max-links\\nodbc.max_links = -1\\n\\n; Handling of LONG fields.  Returns number of bytes to variables.  0 means\\n; passthru.\\n; https:\/\/php.net\/odbc.defaultlrl\\nodbc.defaultlrl = 4096\\n\\n; Handling of binary data.  0 means passthru, 1 return as is, 2 convert to char.\\n; See the documentation on odbc_binmode and odbc_longreadlen for an explanation\\n; of odbc.defaultlrl and odbc.defaultbinmode\\n; https:\/\/php.net\/odbc.defaultbinmode\\nodbc.defaultbinmode = 1\\n\\n[MySQLi]\\n\\n; Maximum number of persistent links.  -1 means no limit.\\n; https:\/\/php.net\/mysqli.max-persistent\\nmysqli.max_persistent = -1\\n\\n; Allow accessing, from PHP's perspective, local files with LOAD DATA statements\\n; https:\/\/php.net\/mysqli.allow_local_infile\\n;mysqli.allow_local_infile = On\\n\\n; It allows the user to specify a folder where files that can be sent via LOAD DATA\\n; LOCAL can exist. It is ignored if mysqli.allow_local_infile is enabled.\\n;mysqli.local_infile_directory =\\n\\n; Allow or prevent persistent links.\\n; https:\/\/php.net\/mysqli.allow-persistent\\nmysqli.allow_persistent = On\\n\\n; Maximum number of links.  -1 means no limit.\\n; https:\/\/php.net\/mysqli.max-links\\nmysqli.max_links = -1\\n\\n; Default port number for mysqli_connect().  If unset, mysqli_connect() will use\\n; the $MYSQL_TCP_PORT or the mysql-tcp entry in \/etc\/services or the\\n; compile-time value defined MYSQL_PORT (in that order).  Win32 will only look\\n; at MYSQL_PORT.\\n; https:\/\/php.net\/mysqli.default-port\\nmysqli.default_port = 3306\\n\\n; Default socket name for local MySQL connects.  If empty, uses the built-in\\n; MySQL defaults.\\n; https:\/\/php.net\/mysqli.default-socket\\nmysqli.default_socket =\\n\\n; Default host for mysqli_connect() (doesn't apply in safe mode).\\n; https:\/\/php.net\/mysqli.default-host\\nmysqli.default_host =\\n\\n; Default user for mysqli_connect() (doesn't apply in safe mode).\\n; https:\/\/php.net\/mysqli.default-user\\nmysqli.default_user =\\n\\n; Default password for mysqli_connect() (doesn't apply in safe mode).\\n; Note that this is generally a *bad* idea to store passwords in this file.\\n; *Any* user with PHP access can run 'echo get_cfg_var(\\\"mysqli.default_pw\\\")\\n; and reveal this password!  And of course, any users with read access to this\\n; file will be able to reveal the password as well.\\n; https:\/\/php.net\/mysqli.default-pw\\nmysqli.default_pw =\\n\\n; Allow or prevent reconnect\\nmysqli.reconnect = Off\\n\\n; If this option is enabled, closing a persistent connection will rollback\\n; any pending transactions of this connection, before it is put back\\n; into the persistent connection pool.\\n;mysqli.rollback_on_cached_plink = Off\\n\\n[mysqlnd]\\n; Enable \/ Disable collection of general statistics by mysqlnd which can be\\n; used to tune and monitor MySQL operations.\\nmysqlnd.collect_statistics = On\\n\\n; Enable \/ Disable collection of memory usage statistics by mysqlnd which can be\\n; used to tune and monitor MySQL operations.\\nmysqlnd.collect_memory_statistics = Off\\n\\n; Records communication from all extensions using mysqlnd to the specified log\\n; file.\\n; https:\/\/php.net\/mysqlnd.debug\\n;mysqlnd.debug =\\n\\n; Defines which queries will be logged.\\n;mysqlnd.log_mask = 0\\n\\n; Default size of the mysqlnd memory pool, which is used by result sets.\\n;mysqlnd.mempool_default_size = 16000\\n\\n; Size of a pre-allocated buffer used when sending commands to MySQL in bytes.\\n;mysqlnd.net_cmd_buffer_size = 2048\\n\\n; Size of a pre-allocated buffer used for reading data sent by the server in\\n; bytes.\\n;mysqlnd.net_read_buffer_size = 32768\\n\\n; Timeout for network requests in seconds.\\n;mysqlnd.net_read_timeout = 31536000\\n\\n; SHA-256 Authentication Plugin related. File with the MySQL server public RSA\\n; key.\\n;mysqlnd.sha256_server_public_key =\\n\\n[OCI8]\\n\\n; Connection: Enables privileged connections using external\\n; credentials (OCI_SYSOPER, OCI_SYSDBA)\\n; https:\/\/php.net\/oci8.privileged-connect\\n;oci8.privileged_connect = Off\\n\\n; Connection: The maximum number of persistent OCI8 connections per\\n; process. Using -1 means no limit.\\n; https:\/\/php.net\/oci8.max-persistent\\n;oci8.max_persistent = -1\\n\\n; Connection: The maximum number of seconds a process is allowed to\\n; maintain an idle persistent connection. Using -1 means idle\\n; persistent connections will be maintained forever.\\n; https:\/\/php.net\/oci8.persistent-timeout\\n;oci8.persistent_timeout = -1\\n\\n; Connection: The number of seconds that must pass before issuing a\\n; ping during oci_pconnect() to check the connection validity. When\\n; set to 0, each oci_pconnect() will cause a ping. Using -1 disables\\n; pings completely.\\n; https:\/\/php.net\/oci8.ping-interval\\n;oci8.ping_interval = 60\\n\\n; Connection: Set this to a user chosen connection class to be used\\n; for all pooled server requests with Oracle Database Resident\\n; Connection Pooling (DRCP).  To use DRCP, this value should be set to\\n; the same string for all web servers running the same application,\\n; the database pool must be configured, and the connection string must\\n; specify to use a pooled server.\\n;oci8.connection_class =\\n\\n; High Availability: Using On lets PHP receive Fast Application\\n; Notification (FAN) events generated when a database node fails. The\\n; database must also be configured to post FAN events.\\n;oci8.events = Off\\n\\n; Tuning: This option enables statement caching, and specifies how\\n; many statements to cache. Using 0 disables statement caching.\\n; https:\/\/php.net\/oci8.statement-cache-size\\n;oci8.statement_cache_size = 20\\n\\n; Tuning: Enables row prefetching and sets the default number of\\n; rows that will be fetched automatically after statement execution.\\n; https:\/\/php.net\/oci8.default-prefetch\\n;oci8.default_prefetch = 100\\n\\n; Tuning: Sets the amount of LOB data that is internally returned from\\n; Oracle Database when an Oracle LOB locator is initially retrieved as\\n; part of a query. Setting this can improve performance by reducing\\n; round-trips.\\n; https:\/\/php.net\/oci8.prefetch-lob-size\\n; oci8.prefetch_lob_size = 0\\n\\n; Compatibility. Using On means oci_close() will not close\\n; oci_connect() and oci_new_connect() connections.\\n; https:\/\/php.net\/oci8.old-oci-close-semantics\\n;oci8.old_oci_close_semantics = Off\\n\\n[PostgreSQL]\\n; Allow or prevent persistent links.\\n; https:\/\/php.net\/pgsql.allow-persistent\\npgsql.allow_persistent = On\\n\\n; Detect broken persistent links always with pg_pconnect().\\n; Auto reset feature requires a little overheads.\\n; https:\/\/php.net\/pgsql.auto-reset-persistent\\npgsql.auto_reset_persistent = Off\\n\\n; Maximum number of persistent links.  -1 means no limit.\\n; https:\/\/php.net\/pgsql.max-persistent\\npgsql.max_persistent = -1\\n\\n; Maximum number of links (persistent+non persistent).  -1 means no limit.\\n; https:\/\/php.net\/pgsql.max-links\\npgsql.max_links = -1\\n\\n; Ignore PostgreSQL backends Notice message or not.\\n; Notice message logging require a little overheads.\\n; https:\/\/php.net\/pgsql.ignore-notice\\npgsql.ignore_notice = 0\\n\\n; Log PostgreSQL backends Notice message or not.\\n; Unless pgsql.ignore_notice=0, module cannot log notice message.\\n; https:\/\/php.net\/pgsql.log-notice\\npgsql.log_notice = 0\\n\\n[bcmath]\\n; Number of decimal digits for all bcmath functions.\\n; https:\/\/php.net\/bcmath.scale\\nbcmath.scale = 0\\n\\n[browscap]\\n; https:\/\/php.net\/browscap\\n;browscap = extra\/browscap.ini\\n\\n[Session]\\n; Handler used to store\/retrieve data.\\n; https:\/\/php.net\/session.save-handler\\nsession.save_handler = files\\n\\n; Argument passed to save_handler.  In the case of files, this is the path\\n; where data files are stored. Note: Windows users have to change this\\n; variable in order to use PHP's session functions.\\n;\\n; The path can be defined as:\\n;\\n;     session.save_path = \\\"N;\/path\\\"\\n;\\n; where N is an integer.  Instead of storing all the session files in\\n; \/path, what this will do is use subdirectories N-levels deep, and\\n; store the session data in those directories.  This is useful if\\n; your OS has problems with many files in one directory, and is\\n; a more efficient layout for servers that handle many sessions.\\n;\\n; NOTE 1: PHP will not create this directory structure automatically.\\n;         You can use the script in the ext\/session dir for that purpose.\\n; NOTE 2: See the section on garbage collection below if you choose to\\n;         use subdirectories for session storage\\n;\\n; The file storage module creates files using mode 600 by default.\\n; You can change that by using\\n;\\n;     session.save_path = \\\"N;MODE;\/path\\\"\\n;\\n; where MODE is the octal representation of the mode. Note that this\\n; does not overwrite the process's umask.\\n; https:\/\/php.net\/session.save-path\\n;session.save_path = \\\"\/var\/lib\/php\/sessions\\\"\\n\\n; Whether to use strict session mode.\\n; Strict session mode does not accept an uninitialized session ID, and\\n; regenerates the session ID if the browser sends an uninitialized session ID.\\n; Strict mode protects applications from session fixation via a session adoption\\n; vulnerability. It is disabled by default for maximum compatibility, but\\n; enabling it is encouraged.\\n; https:\/\/wiki.php.net\/rfc\/strict_sessions\\nsession.use_strict_mode = 0\\n\\n; Whether to use cookies.\\n; https:\/\/php.net\/session.use-cookies\\nsession.use_cookies = 1\\n\\n; https:\/\/php.net\/session.cookie-secure\\n;session.cookie_secure =\\n\\n; This option forces PHP to fetch and use a cookie for storing and maintaining\\n; the session id. We encourage this operation as it's very helpful in combating\\n; session hijacking when not specifying and managing your own session id. It is\\n; not the be-all and end-all of session hijacking defense, but it's a good start.\\n; https:\/\/php.net\/session.use-only-cookies\\nsession.use_only_cookies = 1\\n\\n; Name of the session (used as cookie name).\\n; https:\/\/php.net\/session.name\\nsession.name = PHPSESSID\\n\\n; Initialize session on request startup.\\n; https:\/\/php.net\/session.auto-start\\nsession.auto_start = 0\\n\\n; Lifetime in seconds of cookie or, if 0, until browser is restarted.\\n; https:\/\/php.net\/session.cookie-lifetime\\nsession.cookie_lifetime = 0\\n\\n; The path for which the cookie is valid.\\n; https:\/\/php.net\/session.cookie-path\\nsession.cookie_path = \/\\n\\n; The domain for which the cookie is valid.\\n; https:\/\/php.net\/session.cookie-domain\\nsession.cookie_domain =\\n\\n; Whether or not to add the httpOnly flag to the cookie, which makes it\\n; inaccessible to browser scripting languages such as JavaScript.\\n; https:\/\/php.net\/session.cookie-httponly\\nsession.cookie_httponly =\\n\\n; Add SameSite attribute to cookie to help mitigate Cross-Site Request Forgery (CSRF\/XSRF)\\n; Current valid values are \\\"Strict\\\", \\\"Lax\\\" or \\\"None\\\". When using \\\"None\\\",\\n; make sure to include the quotes, as `none` is interpreted like `false` in ini files.\\n; https:\/\/tools.ietf.org\/html\/draft-west-first-party-cookies-07\\nsession.cookie_samesite =\\n\\n; Handler used to serialize data. php is the standard serializer of PHP.\\n; https:\/\/php.net\/session.serialize-handler\\nsession.serialize_handler = php\\n\\n; Defines the probability that the 'garbage collection' process is started on every\\n; session initialization. The probability is calculated by using gc_probability\/gc_divisor,\\n; e.g. 1\/100 means there is a 1% chance that the GC process starts on each request.\\n; Default Value: 1\\n; Development Value: 1\\n; Production Value: 1\\n; https:\/\/php.net\/session.gc-probability\\nsession.gc_probability = 0\\n\\n; Defines the probability that the 'garbage collection' process is started on every\\n; session initialization. The probability is calculated by using gc_probability\/gc_divisor,\\n; e.g. 1\/100 means there is a 1% chance that the GC process starts on each request.\\n; For high volume production servers, using a value of 1000 is a more efficient approach.\\n; Default Value: 100\\n; Development Value: 1000\\n; Production Value: 1000\\n; https:\/\/php.net\/session.gc-divisor\\nsession.gc_divisor = 1000\\n\\n; After this number of seconds, stored data will be seen as 'garbage' and\\n; cleaned up by the garbage collection process.\\n; https:\/\/php.net\/session.gc-maxlifetime\\nsession.gc_maxlifetime = 1440\\n\\n; NOTE: If you are using the subdirectory option for storing session files\\n;       (see session.save_path above), then garbage collection does *not*\\n;       happen automatically.  You will need to do your own garbage\\n;       collection through a shell script, cron entry, or some other method.\\n;       For example, the following script is the equivalent of setting\\n;       session.gc_maxlifetime to 1440 (1440 seconds = 24 minutes):\\n;          find \/path\/to\/sessions -cmin +24 -type f | xargs rm\\n\\n; Check HTTP Referer to invalidate externally stored URLs containing ids.\\n; HTTP_REFERER has to contain this substring for the session to be\\n; considered as valid.\\n; https:\/\/php.net\/session.referer-check\\nsession.referer_check =\\n\\n; Set to {nocache,private,public,} to determine HTTP caching aspects\\n; or leave this empty to avoid sending anti-caching headers.\\n; https:\/\/php.net\/session.cache-limiter\\nsession.cache_limiter = nocache\\n\\n; Document expires after n minutes.\\n; https:\/\/php.net\/session.cache-expire\\nsession.cache_expire = 180\\n\\n; trans sid support is disabled by default.\\n; Use of trans sid may risk your users' security.\\n; Use this option with caution.\\n; - User may send URL contains active session ID\\n;   to other person via. email\/irc\/etc.\\n; - URL that contains active session ID may be stored\\n;   in publicly accessible computer.\\n; - User may access your site with the same session ID\\n;   always using URL stored in browser's history or bookmarks.\\n; https:\/\/php.net\/session.use-trans-sid\\nsession.use_trans_sid = 0\\n\\n; Set session ID character length. This value could be between 22 to 256.\\n; Shorter length than default is supported only for compatibility reason.\\n; Users should use 32 or more chars.\\n; https:\/\/php.net\/session.sid-length\\n; Default Value: 32\\n; Development Value: 26\\n; Production Value: 26\\nsession.sid_length = 26\\n\\n; The URL rewriter will look for URLs in a defined set of HTML tags.\\n; <form> is special; if you include them here, the rewriter will\\n; add a hidden <input> field with the info which is otherwise appended\\n; to URLs. <form> tag's action attribute URL will not be modified\\n; unless it is specified.\\n; Note that all valid entries require a \\\"=\\\", even if no value follows.\\n; Default Value: \\\"a=href,area=href,frame=src,form=\\\"\\n; Development Value: \\\"a=href,area=href,frame=src,form=\\\"\\n; Production Value: \\\"a=href,area=href,frame=src,form=\\\"\\n; https:\/\/php.net\/url-rewriter.tags\\nsession.trans_sid_tags = \\\"a=href,area=href,frame=src,form=\\\"\\n\\n; URL rewriter does not rewrite absolute URLs by default.\\n; To enable rewrites for absolute paths, target hosts must be specified\\n; at RUNTIME. i.e. use ini_set()\\n; <form> tags is special. PHP will check action attribute's URL regardless\\n; of session.trans_sid_tags setting.\\n; If no host is defined, HTTP_HOST will be used for allowed host.\\n; Example value: php.net,www.php.net,wiki.php.net\\n; Use \\\",\\\" for multiple hosts. No spaces are allowed.\\n; Default Value: \\\"\\\"\\n; Development Value: \\\"\\\"\\n; Production Value: \\\"\\\"\\n;session.trans_sid_hosts=\\\"\\\"\\n\\n; Define how many bits are stored in each character when converting\\n; the binary hash data to something readable.\\n; Possible values:\\n;   4  (4 bits: 0-9, a-f)\\n;   5  (5 bits: 0-9, a-v)\\n;   6  (6 bits: 0-9, a-z, A-Z, \\\"-\\\", \\\",\\\")\\n; Default Value: 4\\n; Development Value: 5\\n; Production Value: 5\\n; https:\/\/php.net\/session.hash-bits-per-character\\nsession.sid_bits_per_character = 5\\n\\n; Enable upload progress tracking in $_SESSION\\n; Default Value: On\\n; Development Value: On\\n; Production Value: On\\n; https:\/\/php.net\/session.upload-progress.enabled\\n;session.upload_progress.enabled = On\\n\\n; Cleanup the progress information as soon as all POST data has been read\\n; (i.e. upload completed).\\n; Default Value: On\\n; Development Value: On\\n; Production Value: On\\n; https:\/\/php.net\/session.upload-progress.cleanup\\n;session.upload_progress.cleanup = On\\n\\n; A prefix used for the upload progress key in $_SESSION\\n; Default Value: \\\"upload_progress_\\\"\\n; Development Value: \\\"upload_progress_\\\"\\n; Production Value: \\\"upload_progress_\\\"\\n; https:\/\/php.net\/session.upload-progress.prefix\\n;session.upload_progress.prefix = \\\"upload_progress_\\\"\\n\\n; The index name (concatenated with the prefix) in $_SESSION\\n; containing the upload progress information\\n; Default Value: \\\"PHP_SESSION_UPLOAD_PROGRESS\\\"\\n; Development Value: \\\"PHP_SESSION_UPLOAD_PROGRESS\\\"\\n; Production Value: \\\"PHP_SESSION_UPLOAD_PROGRESS\\\"\\n; https:\/\/php.net\/session.upload-progress.name\\n;session.upload_progress.name = \\\"PHP_SESSION_UPLOAD_PROGRESS\\\"\\n\\n; How frequently the upload progress should be updated.\\n; Given either in percentages (per-file), or in bytes\\n; Default Value: \\\"1%\\\"\\n; Development Value: \\\"1%\\\"\\n; Production Value: \\\"1%\\\"\\n; https:\/\/php.net\/session.upload-progress.freq\\n;session.upload_progress.freq =  \\\"1%\\\"\\n\\n; The minimum delay between updates, in seconds\\n; Default Value: 1\\n; Development Value: 1\\n; Production Value: 1\\n; https:\/\/php.net\/session.upload-progress.min-freq\\n;session.upload_progress.min_freq = \\\"1\\\"\\n\\n; Only write session data when session data is changed. Enabled by default.\\n; https:\/\/php.net\/session.lazy-write\\n;session.lazy_write = On\\n\\n[Assertion]\\n; Switch whether to compile assertions at all (to have no overhead at run-time)\\n; -1: Do not compile at all\\n;  0: Jump over assertion at run-time\\n;  1: Execute assertions\\n; Changing from or to a negative value is only possible in php.ini! (For turning assertions on and off at run-time, see assert.active, when zend.assertions = 1)\\n; Default Value: 1\\n; Development Value: 1\\n; Production Value: -1\\n; https:\/\/php.net\/zend.assertions\\nzend.assertions = -1\\n\\n; Assert(expr); active by default.\\n; https:\/\/php.net\/assert.active\\n;assert.active = On\\n\\n; Throw an AssertionError on failed assertions\\n; https:\/\/php.net\/assert.exception\\n;assert.exception = On\\n\\n; Issue a PHP warning for each failed assertion. (Overridden by assert.exception if active)\\n; https:\/\/php.net\/assert.warning\\n;assert.warning = On\\n\\n; Don't bail out by default.\\n; https:\/\/php.net\/assert.bail\\n;assert.bail = Off\\n\\n; User-function to be called if an assertion fails.\\n; https:\/\/php.net\/assert.callback\\n;assert.callback = 0\\n\\n[COM]\\n; path to a file containing GUIDs, IIDs or filenames of files with TypeLibs\\n; https:\/\/php.net\/com.typelib-file\\n;com.typelib_file =\\n\\n; allow Distributed-COM calls\\n; https:\/\/php.net\/com.allow-dcom\\n;com.allow_dcom = true\\n\\n; autoregister constants of a component's typelib on com_load()\\n; https:\/\/php.net\/com.autoregister-typelib\\n;com.autoregister_typelib = true\\n\\n; register constants casesensitive\\n; https:\/\/php.net\/com.autoregister-casesensitive\\n;com.autoregister_casesensitive = false\\n\\n; show warnings on duplicate constant registrations\\n; https:\/\/php.net\/com.autoregister-verbose\\n;com.autoregister_verbose = true\\n\\n; The default character set code-page to use when passing strings to and from COM objects.\\n; Default: system ANSI code page\\n;com.code_page=\\n\\n; The version of the .NET framework to use. The value of the setting are the first three parts\\n; of the framework's version number, separated by dots, and prefixed with \\\"v\\\", e.g. \\\"v4.0.30319\\\".\\n;com.dotnet_version=\\n\\n[mbstring]\\n; language for internal character representation.\\n; This affects mb_send_mail() and mbstring.detect_order.\\n; https:\/\/php.net\/mbstring.language\\n;mbstring.language = Japanese\\n\\n; Use of this INI entry is deprecated, use global internal_encoding instead.\\n; internal\/script encoding.\\n; Some encoding cannot work as internal encoding. (e.g. SJIS, BIG5, ISO-2022-*)\\n; If empty, default_charset or internal_encoding or iconv.internal_encoding is used.\\n; The precedence is: default_charset < internal_encoding < iconv.internal_encoding\\n;mbstring.internal_encoding =\\n\\n; Use of this INI entry is deprecated, use global input_encoding instead.\\n; http input encoding.\\n; mbstring.encoding_translation = On is needed to use this setting.\\n; If empty, default_charset or input_encoding or mbstring.input is used.\\n; The precedence is: default_charset < input_encoding < mbstring.http_input\\n; https:\/\/php.net\/mbstring.http-input\\n;mbstring.http_input =\\n\\n; Use of this INI entry is deprecated, use global output_encoding instead.\\n; http output encoding.\\n; mb_output_handler must be registered as output buffer to function.\\n; If empty, default_charset or output_encoding or mbstring.http_output is used.\\n; The precedence is: default_charset < output_encoding < mbstring.http_output\\n; To use an output encoding conversion, mbstring's output handler must be set\\n; otherwise output encoding conversion cannot be performed.\\n; https:\/\/php.net\/mbstring.http-output\\n;mbstring.http_output =\\n\\n; enable automatic encoding translation according to\\n; mbstring.internal_encoding setting. Input chars are\\n; converted to internal encoding by setting this to On.\\n; Note: Do _not_ use automatic encoding translation for\\n;       portable libs\/applications.\\n; https:\/\/php.net\/mbstring.encoding-translation\\n;mbstring.encoding_translation = Off\\n\\n; automatic encoding detection order.\\n; \\\"auto\\\" detect order is changed according to mbstring.language\\n; https:\/\/php.net\/mbstring.detect-order\\n;mbstring.detect_order = auto\\n\\n; substitute_character used when character cannot be converted\\n; one from another\\n; https:\/\/php.net\/mbstring.substitute-character\\n;mbstring.substitute_character = none\\n\\n; Enable strict encoding detection.\\n;mbstring.strict_detection = Off\\n\\n; This directive specifies the regex pattern of content types for which mb_output_handler()\\n; is activated.\\n; Default: mbstring.http_output_conv_mimetypes=^(text\/|application\/xhtml\\\\+xml)\\n;mbstring.http_output_conv_mimetypes=\\n\\n; This directive specifies maximum stack depth for mbstring regular expressions. It is similar\\n; to the pcre.recursion_limit for PCRE.\\n;mbstring.regex_stack_limit=100000\\n\\n; This directive specifies maximum retry count for mbstring regular expressions. It is similar\\n; to the pcre.backtrack_limit for PCRE.\\n;mbstring.regex_retry_limit=1000000\\n\\n[gd]\\n; Tell the jpeg decode to ignore warnings and try to create\\n; a gd image. The warning will then be displayed as notices\\n; disabled by default\\n; https:\/\/php.net\/gd.jpeg-ignore-warning\\n;gd.jpeg_ignore_warning = 1\\n\\n[exif]\\n; Exif UNICODE user comments are handled as UCS-2BE\/UCS-2LE and JIS as JIS.\\n; With mbstring support this will automatically be converted into the encoding\\n; given by corresponding encode setting. When empty mbstring.internal_encoding\\n; is used. For the decode settings you can distinguish between motorola and\\n; intel byte order. A decode setting cannot be empty.\\n; https:\/\/php.net\/exif.encode-unicode\\n;exif.encode_unicode = ISO-8859-15\\n\\n; https:\/\/php.net\/exif.decode-unicode-motorola\\n;exif.decode_unicode_motorola = UCS-2BE\\n\\n; https:\/\/php.net\/exif.decode-unicode-intel\\n;exif.decode_unicode_intel    = UCS-2LE\\n\\n; https:\/\/php.net\/exif.encode-jis\\n;exif.encode_jis =\\n\\n; https:\/\/php.net\/exif.decode-jis-motorola\\n;exif.decode_jis_motorola = JIS\\n\\n; https:\/\/php.net\/exif.decode-jis-intel\\n;exif.decode_jis_intel    = JIS\\n\\n[Tidy]\\n; The path to a default tidy configuration file to use when using tidy\\n; https:\/\/php.net\/tidy.default-config\\n;tidy.default_config = \/usr\/local\/lib\/php\/default.tcfg\\n\\n; Should tidy clean and repair output automatically?\\n; WARNING: Do not use this option if you are generating non-html content\\n; such as dynamic images\\n; https:\/\/php.net\/tidy.clean-output\\ntidy.clean_output = Off\\n\\n[soap]\\n; Enables or disables WSDL caching feature.\\n; https:\/\/php.net\/soap.wsdl-cache-enabled\\nsoap.wsdl_cache_enabled=1\\n\\n; Sets the directory name where SOAP extension will put cache files.\\n; https:\/\/php.net\/soap.wsdl-cache-dir\\nsoap.wsdl_cache_dir=\\\"\/tmp\\\"\\n\\n; (time to live) Sets the number of second while cached file will be used\\n; instead of original one.\\n; https:\/\/php.net\/soap.wsdl-cache-ttl\\nsoap.wsdl_cache_ttl=86400\\n\\n; Sets the size of the cache limit. (Max. number of WSDL files to cache)\\nsoap.wsdl_cache_limit = 5\\n\\n[sysvshm]\\n; A default size of the shared memory segment\\n;sysvshm.init_mem = 10000\\n\\n[ldap]\\n; Sets the maximum number of open links or -1 for unlimited.\\nldap.max_links = -1\\n\\n[dba]\\n;dba.default_handler=\\n\\n[opcache]\\n; Determines if Zend OPCache is enabled\\n;opcache.enable=1\\n\\n; Determines if Zend OPCache is enabled for the CLI version of PHP\\n;opcache.enable_cli=0\\n\\n; The OPcache shared memory storage size.\\n;opcache.memory_consumption=128\\n\\n; The amount of memory for interned strings in Mbytes.\\n;opcache.interned_strings_buffer=8\\n\\n; The maximum number of keys (scripts) in the OPcache hash table.\\n; Only numbers between 200 and 1000000 are allowed.\\n;opcache.max_accelerated_files=10000\\n\\n; The maximum percentage of \\\"wasted\\\" memory until a restart is scheduled.\\n;opcache.max_wasted_percentage=5\\n\\n; When this directive is enabled, the OPcache appends the current working\\n; directory to the script key, thus eliminating possible collisions between\\n; files with the same name (basename). Disabling the directive improves\\n; performance, but may break existing applications.\\n;opcache.use_cwd=1\\n\\n; When disabled, you must reset the OPcache manually or restart the\\n; webserver for changes to the filesystem to take effect.\\n;opcache.validate_timestamps=1\\n\\n; How often (in seconds) to check file timestamps for changes to the shared\\n; memory storage allocation. (\\\"1\\\" means validate once per second, but only\\n; once per request. \\\"0\\\" means always validate)\\n;opcache.revalidate_freq=2\\n\\n; Enables or disables file search in include_path optimization\\n;opcache.revalidate_path=0\\n\\n; If disabled, all PHPDoc comments are dropped from the code to reduce the\\n; size of the optimized code.\\n;opcache.save_comments=1\\n\\n; If enabled, compilation warnings (including notices and deprecations) will\\n; be recorded and replayed each time a file is included. Otherwise, compilation\\n; warnings will only be emitted when the file is first cached.\\n;opcache.record_warnings=0\\n\\n; Allow file existence override (file_exists, etc.) performance feature.\\n;opcache.enable_file_override=0\\n\\n; A bitmask, where each bit enables or disables the appropriate OPcache\\n; passes\\n;opcache.optimization_level=0x7FFFBFFF\\n\\n;opcache.dups_fix=0\\n\\n; The location of the OPcache blacklist file (wildcards allowed).\\n; Each OPcache blacklist file is a text file that holds the names of files\\n; that should not be accelerated. The file format is to add each filename\\n; to a new line. The filename may be a full path or just a file prefix\\n; (i.e., \/var\/www\/x  blacklists all the files and directories in \/var\/www\\n; that start with 'x'). Line starting with a ; are ignored (comments).\\n;opcache.blacklist_filename=\\n\\n; Allows exclusion of large files from being cached. By default all files\\n; are cached.\\n;opcache.max_file_size=0\\n\\n; Check the cache checksum each N requests.\\n; The default value of \\\"0\\\" means that the checks are disabled.\\n;opcache.consistency_checks=0\\n\\n; How long to wait (in seconds) for a scheduled restart to begin if the cache\\n; is not being accessed.\\n;opcache.force_restart_timeout=180\\n\\n; OPcache error_log file name. Empty string assumes \\\"stderr\\\".\\n;opcache.error_log=\\n\\n; All OPcache errors go to the Web server log.\\n; By default, only fatal errors (level 0) or errors (level 1) are logged.\\n; You can also enable warnings (level 2), info messages (level 3) or\\n; debug messages (level 4).\\n;opcache.log_verbosity_level=1\\n\\n; Preferred Shared Memory back-end. Leave empty and let the system decide.\\n;opcache.preferred_memory_model=\\n\\n; Protect the shared memory from unexpected writing during script execution.\\n; Useful for internal debugging only.\\n;opcache.protect_memory=0\\n\\n; Allows calling OPcache API functions only from PHP scripts which path is\\n; started from specified string. The default \\\"\\\" means no restriction\\n;opcache.restrict_api=\\n\\n; Mapping base of shared memory segments (for Windows only). All the PHP\\n; processes have to map shared memory into the same address space. This\\n; directive allows to manually fix the \\\"Unable to reattach to base address\\\"\\n; errors.\\n;opcache.mmap_base=\\n\\n; Facilitates multiple OPcache instances per user (for Windows only). All PHP\\n; processes with the same cache ID and user share an OPcache instance.\\n;opcache.cache_id=\\n\\n; Enables and sets the second level cache directory.\\n; It should improve performance when SHM memory is full, at server restart or\\n; SHM reset. The default \\\"\\\" disables file based caching.\\n;opcache.file_cache=\\n\\n; Enables or disables opcode caching in shared memory.\\n;opcache.file_cache_only=0\\n\\n; Enables or disables checksum validation when script loaded from file cache.\\n;opcache.file_cache_consistency_checks=1\\n\\n; Implies opcache.file_cache_only=1 for a certain process that failed to\\n; reattach to the shared memory (for Windows only). Explicitly enabled file\\n; cache is required.\\n;opcache.file_cache_fallback=1\\n\\n; Enables or disables copying of PHP code (text segment) into HUGE PAGES.\\n; Under certain circumstances (if only a single global PHP process is\\n; started from which all others fork), this can increase performance\\n; by a tiny amount because TLB misses are reduced.  On the other hand, this\\n; delays PHP startup, increases memory usage and degrades performance\\n; under memory pressure - use with care.\\n; Requires appropriate OS configuration.\\n;opcache.huge_code_pages=0\\n\\n; Validate cached file permissions.\\n;opcache.validate_permission=0\\n\\n; Prevent name collisions in chroot'ed environment.\\n;opcache.validate_root=0\\n\\n; If specified, it produces opcode dumps for debugging different stages of\\n; optimizations.\\n;opcache.opt_debug_level=0\\n\\n; Specifies a PHP script that is going to be compiled and executed at server\\n; start-up.\\n; https:\/\/php.net\/opcache.preload\\n;opcache.preload=\\n\\n; Preloading code as root is not allowed for security reasons. This directive\\n; facilitates to let the preloading to be run as another user.\\n; https:\/\/php.net\/opcache.preload_user\\n;opcache.preload_user=\\n\\n; Prevents caching files that are less than this number of seconds old. It\\n; protects from caching of incompletely updated files. In case all file updates\\n; on your site are atomic, you may increase performance by setting it to \\\"0\\\".\\n;opcache.file_update_protection=2\\n\\n; Absolute path used to store shared lockfiles (for *nix only).\\n;opcache.lockfile_path=\/tmp\\n\\n[curl]\\n; A default value for the CURLOPT_CAINFO option. This is required to be an\\n; absolute path.\\n;curl.cainfo =\\n\\n[openssl]\\n; The location of a Certificate Authority (CA) file on the local filesystem\\n; to use when verifying the identity of SSL\/TLS peers. Most users should\\n; not specify a value for this directive as PHP will attempt to use the\\n; OS-managed cert stores in its absence. If specified, this value may still\\n; be overridden on a per-stream basis via the \\\"cafile\\\" SSL stream context\\n; option.\\n;openssl.cafile=\\n\\n; If openssl.cafile is not specified or if the CA file is not found, the\\n; directory pointed to by openssl.capath is searched for a suitable\\n; certificate. This value must be a correctly hashed certificate directory.\\n; Most users should not specify a value for this directive as PHP will\\n; attempt to use the OS-managed cert stores in its absence. If specified,\\n; this value may still be overridden on a per-stream basis via the \\\"capath\\\"\\n; SSL stream context option.\\n;openssl.capath=\\n\\n[ffi]\\n; FFI API restriction. Possible values:\\n; \\\"preload\\\" - enabled in CLI scripts and preloaded files (default)\\n; \\\"false\\\"   - always disabled\\n; \\\"true\\\"    - always enabled\\n;ffi.enable=preload\\n\\n; List of headers files to preload, wildcard patterns allowed.\\n;ffi.preload=\"}"}]
JSON;

        $fields = json_decode($fieldDetails);
        return json_encode(self::updateFieldOptions($fields, $data));
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function reload (): bool
    {
        return $this->signalSystemDService("php{$this->phpVersion()}-fpm", self::SystemDSignalReload);
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function stop (): bool
    {
        return $this->signalSystemDService("php{$this->phpVersion()}-fpm", self::SystemDSignalStop);
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function start (): bool
    {
        return $this->signalSystemDService("php{$this->phpVersion()}-fpm", self::SystemDSignalStart);
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function isStatus (string $statusString): bool
    {
        $status = '';
        $php = "php{$this->phpVersion()}-fpm";
        if (CloudAppSignalInterface::STATUS_RUNNING === $statusString) {
            $this->runCommand(function ($out) use (&$status) { $status = $out; }, null, "bash", "-c", "systemctl show $php -p ActiveState");
            return str_starts_with($status, 'ActiveState=active');
        }

        if (CloudAppSignalInterface::STATUS_STOPPED === $statusString) {
            $this->runCommand(function ($out) use (&$status) { $status = $out; }, null, "bash", "-c", "systemctl show $php -p ActiveState");
            return str_starts_with($status, 'ActiveState=inactive');
        }

        return false;
    }

    /**
     * @return string
     */
    public static function PHP_FPM (): string
    {
        return <<<'FPM'
;;;;;;;;;;;;;;;;;;;;;
; FPM Configuration ;
;;;;;;;;;;;;;;;;;;;;;

; All relative paths in this configuration file are relative to PHP's install
; prefix (/usr). This prefix can be dynamically changed by using the
; '-p' argument from the command line.

;;;;;;;;;;;;;;;;;;
; Global Options ;
;;;;;;;;;;;;;;;;;;

[global]
; Pid file
; Note: the default prefix is /var
; Default Value: none
; Warning: if you change the value here, you need to modify systemd
; service PIDFile= setting to match the value here.
pid = /run/php/php[[PHP_VERSION]]-fpm.pid

; Error log file
; If it's set to "syslog", log is sent to syslogd instead of being written
; into a local file.
; Note: the default prefix is /var
; Default Value: log/php-fpm.log
error_log = /var/log/php[[PHP_VERSION]]-fpm.log

; syslog_facility is used to specify what type of program is logging the
; message. This lets syslogd specify that messages from different facilities
; will be handled differently.
; See syslog(3) for possible values (ex daemon equiv LOG_DAEMON)
; Default Value: daemon
;syslog.facility = daemon

; syslog_ident is prepended to every message. If you have multiple FPM
; instances running on the same server, you can change the default value
; which must suit common needs.
; Default Value: php-fpm
;syslog.ident = php-fpm

; Log level
; Possible Values: alert, error, warning, notice, debug
; Default Value: notice
;log_level = notice

; Log limit on number of characters in the single line (log entry). If the
; line is over the limit, it is wrapped on multiple lines. The limit is for
; all logged characters including message prefix and suffix if present. However
; the new line character does not count into it as it is present only when
; logging to a file descriptor. It means the new line character is not present
; when logging to syslog.
; Default Value: 1024
;log_limit = 4096

; Log buffering specifies if the log line is buffered which means that the
; line is written in a single write operation. If the value is false, then the
; data is written directly into the file descriptor. It is an experimental
; option that can potentially improve logging performance and memory usage
; for some heavy logging scenarios. This option is ignored if logging to syslog
; as it has to be always buffered.
; Default value: yes
;log_buffering = no

; If this number of child processes exit with SIGSEGV or SIGBUS within the time
; interval set by emergency_restart_interval then FPM will restart. A value
; of '0' means 'Off'.
; Default Value: 0
;emergency_restart_threshold = 0

; Interval of time used by emergency_restart_interval to determine when
; a graceful restart will be initiated.  This can be useful to work around
; accidental corruptions in an accelerator's shared memory.
; Available Units: s(econds), m(inutes), h(ours), or d(ays)
; Default Unit: seconds
; Default Value: 0
;emergency_restart_interval = 0

; Time limit for child processes to wait for a reaction on signals from master.
; Available units: s(econds), m(inutes), h(ours), or d(ays)
; Default Unit: seconds
; Default Value: 0
;process_control_timeout = 0

; The maximum number of processes FPM will fork. This has been designed to control
; the global number of processes when using dynamic PM within a lot of pools.
; Use it with caution.
; Note: A value of 0 indicates no limit
; Default Value: 0
; process.max = 128

; Specify the nice(2) priority to apply to the master process (only if set)
; The value can vary from -19 (highest priority) to 20 (lowest priority)
; Note: - It will only work if the FPM master process is launched as root
;       - The pool process will inherit the master process priority
;         unless specified otherwise
; Default Value: no set
; process.priority = -19

; Send FPM to background. Set to 'no' to keep FPM in foreground for debugging.
; Default Value: yes
;daemonize = yes

; Set open file descriptor rlimit for the master process.
; Default Value: system defined value
;rlimit_files = 1024

; Set max core size rlimit for the master process.
; Possible Values: 'unlimited' or an integer greater or equal to 0
; Default Value: system defined value
;rlimit_core = 0

; Specify the event mechanism FPM will use. The following is available:
; - select     (any POSIX os)
; - poll       (any POSIX os)
; - epoll      (linux >= 2.5.44)
; - kqueue     (FreeBSD >= 4.1, OpenBSD >= 2.9, NetBSD >= 2.0)
; - /dev/poll  (Solaris >= 7)
; - port       (Solaris >= 10)
; Default Value: not set (auto detection)
;events.mechanism = epoll

; When FPM is built with systemd integration, specify the interval,
; in seconds, between health report notification to systemd.
; Set to 0 to disable.
; Available Units: s(econds), m(inutes), h(ours)
; Default Unit: seconds
; Default value: 10
;systemd_interval = 10

;;;;;;;;;;;;;;;;;;;;
; Pool Definitions ;
;;;;;;;;;;;;;;;;;;;;

; Multiple pools of child processes may be started with different listening
; ports and different management options.  The name of the pool will be
; used in logs and stats. There is no limitation on the number of pools which
; FPM can handle. Your system will tell you anyway :)

; Include one or more files. If glob(3) exists, it is used to include a bunch of
; files from a glob(3) pattern. This directive can be used everywhere in the
; file.
; Relative path can also be used. They will be prefixed by:
;  - the global prefix if it's been set (-p argument)
;  - /usr otherwise
include=/etc/php/[[PHP_VERSION]]/fpm/pool.d/*.conf
FPM;

    }

    /**
     * @return string
     */
    public static function INI_OPTIMIZED (): string
    {
        return <<<'INI'
[PHP]

;;;;;;;;;;;;;;;;;;;
; About php.ini   ;
;;;;;;;;;;;;;;;;;;;
; PHP's initialization file, generally called php.ini, is responsible for
; configuring many of the aspects of PHP's behavior.

; PHP attempts to find and load this configuration from a number of locations.
; The following is a summary of its search order:
; 1. SAPI module specific location.
; 2. The PHPRC environment variable.
; 3. A number of predefined registry keys on Windows
; 4. Current working directory (except CLI)
; 5. The web server's directory (for SAPI modules), or directory of PHP
; (otherwise in Windows)
; 6. The directory from the --with-config-file-path compile time option, or the
; Windows directory (usually C:\windows)
; See the PHP docs for more specific information.
; https://php.net/configuration.file

; The syntax of the file is extremely simple.  Whitespace and lines
; beginning with a semicolon are silently ignored (as you probably guessed).
; Section headers (e.g. [Foo]) are also silently ignored, even though
; they might mean something in the future.

; Directives following the section heading [PATH=/www/mysite] only
; apply to PHP files in the /www/mysite directory.  Directives
; following the section heading [HOST=www.example.com] only apply to
; PHP files served from www.example.com.  Directives set in these
; special sections cannot be overridden by user-defined INI files or
; at runtime. Currently, [PATH=] and [HOST=] sections only work under
; CGI/FastCGI.
; https://php.net/ini.sections

; Directives are specified using the following syntax:
; directive = value
; Directive names are *case sensitive* - foo=bar is different from FOO=bar.
; Directives are variables used to configure PHP or PHP extensions.
; There is no name validation.  If PHP can't find an expected
; directive because it is not set or is mistyped, a default value will be used.

; The value can be a string, a number, a PHP constant (e.g. E_ALL or M_PI), one
; of the INI constants (On, Off, True, False, Yes, No and None) or an expression
; (e.g. E_ALL & ~E_NOTICE), a quoted string ("bar"), or a reference to a
; previously set variable or directive (e.g. ${foo})

; Expressions in the INI file are limited to bitwise operators and parentheses:
; |  bitwise OR
; ^  bitwise XOR
; &  bitwise AND
; ~  bitwise NOT
; !  boolean NOT

; Boolean flags can be turned on using the values 1, On, True or Yes.
; They can be turned off using the values 0, Off, False or No.

; An empty string can be denoted by simply not writing anything after the equal
; sign, or by using the None keyword:

; foo =         ; sets foo to an empty string
; foo = None    ; sets foo to an empty string
; foo = "None"  ; sets foo to the string 'None'

; If you use constants in your value, and these constants belong to a
; dynamically loaded extension (either a PHP extension or a Zend extension),
; you may only use these constants *after* the line that loads the extension.

;;;;;;;;;;;;;;;;;;;
; About this file ;
;;;;;;;;;;;;;;;;;;;
; PHP comes packaged with two INI files. One that is recommended to be used
; in production environments and one that is recommended to be used in
; development environments.

; php.ini-production contains settings which hold security, performance and
; best practices at its core. But please be aware, these settings may break
; compatibility with older or less security conscience applications. We
; recommending using the production ini in production and testing environments.

; php.ini-development is very similar to its production variant, except it is
; much more verbose when it comes to errors. We recommend using the
; development version only in development environments, as errors shown to
; application users can inadvertently leak otherwise secure information.

; This is the php.ini-production INI file.

;;;;;;;;;;;;;;;;;;;
; Quick Reference ;
;;;;;;;;;;;;;;;;;;;

; The following are all the settings which are different in either the production
; or development versions of the INIs with respect to PHP's default behavior.
; Please see the actual settings later in the document for more details as to why
; we recommend these changes in PHP's behavior.

; display_errors
;   Default Value: On
;   Development Value: On
;   Production Value: Off

; display_startup_errors
;   Default Value: On
;   Development Value: On
;   Production Value: Off

; error_reporting
;   Default Value: E_ALL
;   Development Value: E_ALL
;   Production Value: E_ALL & ~E_DEPRECATED & ~E_STRICT

; log_errors
;   Default Value: Off
;   Development Value: On
;   Production Value: On

; max_input_time
;   Default Value: -1 (Unlimited)
;   Development Value: 60 (60 seconds)
;   Production Value: 60 (60 seconds)

; output_buffering
;   Default Value: Off
;   Development Value: 4096
;   Production Value: 4096

; register_argc_argv
;   Default Value: On
;   Development Value: Off
;   Production Value: Off

; request_order
;   Default Value: None
;   Development Value: "GP"
;   Production Value: "GP"

; session.gc_divisor
;   Default Value: 100
;   Development Value: 1000
;   Production Value: 1000

; session.sid_bits_per_character
;   Default Value: 4
;   Development Value: 5
;   Production Value: 5

; short_open_tag
;   Default Value: On
;   Development Value: Off
;   Production Value: Off

; variables_order
;   Default Value: "EGPCS"
;   Development Value: "GPCS"
;   Production Value: "GPCS"

; zend.exception_ignore_args
;   Default Value: Off
;   Development Value: Off
;   Production Value: On

; zend.exception_string_param_max_len
;   Default Value: 15
;   Development Value: 15
;   Production Value: 0

;;;;;;;;;;;;;;;;;;;;
; php.ini Options  ;
;;;;;;;;;;;;;;;;;;;;
; Name for user-defined php.ini (.htaccess) files. Default is ".user.ini"
;user_ini.filename = ".user.ini"

; To disable this feature set this option to an empty value
;user_ini.filename =

; TTL for user-defined php.ini files (time-to-live) in seconds. Default is 300 seconds (5 minutes)
;user_ini.cache_ttl = 300

;;;;;;;;;;;;;;;;;;;;
; Language Options ;
;;;;;;;;;;;;;;;;;;;;

; Enable the PHP scripting language engine under Apache.
; https://php.net/engine
engine = On

; This directive determines whether or not PHP will recognize code between
; <? and ?> tags as PHP source which should be processed as such. It is
; generally recommended that <?php and ?> should be used and that this feature
; should be disabled, as enabling it may result in issues when generating XML
; documents, however this remains supported for backward compatibility reasons.
; Note that this directive does not control the <?= shorthand tag, which can be
; used regardless of this directive.
; Default Value: On
; Development Value: Off
; Production Value: Off
; https://php.net/short-open-tag
short_open_tag = Off

; The number of significant digits displayed in floating point numbers.
; https://php.net/precision
precision = 14

; Output buffering is a mechanism for controlling how much output data
; (excluding headers and cookies) PHP should keep internally before pushing that
; data to the client. If your application's output exceeds this setting, PHP
; will send that data in chunks of roughly the size you specify.
; Turning on this setting and managing its maximum buffer size can yield some
; interesting side-effects depending on your application and web server.
; You may be able to send headers and cookies after you've already sent output
; through print or echo. You also may see performance benefits if your server is
; emitting less packets due to buffered output versus PHP streaming the output
; as it gets it. On production servers, 4096 bytes is a good setting for performance
; reasons.
; Note: Output buffering can also be controlled via Output Buffering Control
;   functions.
; Possible Values:
;   On = Enabled and buffer is unlimited. (Use with caution)
;   Off = Disabled
;   Integer = Enables the buffer and sets its maximum size in bytes.
; Note: This directive is hardcoded to Off for the CLI SAPI
; Default Value: Off
; Development Value: 4096
; Production Value: 4096
; https://php.net/output-buffering
output_buffering = 4096

; You can redirect all of the output of your scripts to a function.  For
; example, if you set output_handler to "mb_output_handler", character
; encoding will be transparently converted to the specified encoding.
; Setting any output handler automatically turns on output buffering.
; Note: People who wrote portable scripts should not depend on this ini
;   directive. Instead, explicitly set the output handler using ob_start().
;   Using this ini directive may cause problems unless you know what script
;   is doing.
; Note: You cannot use both "mb_output_handler" with "ob_iconv_handler"
;   and you cannot use both "ob_gzhandler" and "zlib.output_compression".
; Note: output_handler must be empty if this is set 'On' !!!!
;   Instead you must use zlib.output_handler.
; https://php.net/output-handler
;output_handler =

; URL rewriter function rewrites URL on the fly by using
; output buffer. You can set target tags by this configuration.
; "form" tag is special tag. It will add hidden input tag to pass values.
; Refer to session.trans_sid_tags for usage.
; Default Value: "form="
; Development Value: "form="
; Production Value: "form="
;url_rewriter.tags

; URL rewriter will not rewrite absolute URL nor form by default. To enable
; absolute URL rewrite, allowed hosts must be defined at RUNTIME.
; Refer to session.trans_sid_hosts for more details.
; Default Value: ""
; Development Value: ""
; Production Value: ""
;url_rewriter.hosts

; Transparent output compression using the zlib library
; Valid values for this option are 'off', 'on', or a specific buffer size
; to be used for compression (default is 4KB)
; Note: Resulting chunk size may vary due to nature of compression. PHP
;   outputs chunks that are few hundreds bytes each as a result of
;   compression. If you prefer a larger chunk size for better
;   performance, enable output_buffering in addition.
; Note: You need to use zlib.output_handler instead of the standard
;   output_handler, or otherwise the output will be corrupted.
; https://php.net/zlib.output-compression
zlib.output_compression = Off

; https://php.net/zlib.output-compression-level
;zlib.output_compression_level = -1

; You cannot specify additional output handlers if zlib.output_compression
; is activated here. This setting does the same as output_handler but in
; a different order.
; https://php.net/zlib.output-handler
;zlib.output_handler =

; Implicit flush tells PHP to tell the output layer to flush itself
; automatically after every output block.  This is equivalent to calling the
; PHP function flush() after each and every call to print() or echo() and each
; and every HTML block.  Turning this option on has serious performance
; implications and is generally recommended for debugging purposes only.
; https://php.net/implicit-flush
; Note: This directive is hardcoded to On for the CLI SAPI
implicit_flush = Off

; The unserialize callback function will be called (with the undefined class'
; name as parameter), if the unserializer finds an undefined class
; which should be instantiated. A warning appears if the specified function is
; not defined, or if the function doesn't include/implement the missing class.
; So only set this entry, if you really want to implement such a
; callback-function.
unserialize_callback_func =

; The unserialize_max_depth specifies the default depth limit for unserialized
; structures. Setting the depth limit too high may result in stack overflows
; during unserialization. The unserialize_max_depth ini setting can be
; overridden by the max_depth option on individual unserialize() calls.
; A value of 0 disables the depth limit.
;unserialize_max_depth = 4096

; When floats & doubles are serialized, store serialize_precision significant
; digits after the floating point. The default value ensures that when floats
; are decoded with unserialize, the data will remain the same.
; The value is also used for json_encode when encoding double values.
; If -1 is used, then dtoa mode 0 is used which automatically select the best
; precision.
serialize_precision = -1

; open_basedir, if set, limits all file operations to the defined directory
; and below.  This directive makes most sense if used in a per-directory
; or per-virtualhost web server configuration file.
; Note: disables the realpath cache
; https://php.net/open-basedir
;open_basedir =

; This directive allows you to disable certain functions.
; It receives a comma-delimited list of function names.
; https://php.net/disable-functions
disable_functions =

; This directive allows you to disable certain classes.
; It receives a comma-delimited list of class names.
; https://php.net/disable-classes
disable_classes =

; Colors for Syntax Highlighting mode.  Anything that's acceptable in
; <span style="color: ???????"> would work.
; https://php.net/syntax-highlighting
;highlight.string  = #DD0000
;highlight.comment = #FF9900
;highlight.keyword = #007700
;highlight.default = #0000BB
;highlight.html    = #000000

; If enabled, the request will be allowed to complete even if the user aborts
; the request. Consider enabling it if executing long requests, which may end up
; being interrupted by the user or a browser timing out. PHP's default behavior
; is to disable this feature.
; https://php.net/ignore-user-abort
;ignore_user_abort = On

; Determines the size of the realpath cache to be used by PHP. This value should
; be increased on systems where PHP opens many files to reflect the quantity of
; the file operations performed.
; Note: if open_basedir is set, the cache is disabled
; https://php.net/realpath-cache-size
;realpath_cache_size = 4096k

; Duration of time, in seconds for which to cache realpath information for a given
; file or directory. For systems with rarely changing files, consider increasing this
; value.
; https://php.net/realpath-cache-ttl
;realpath_cache_ttl = 120

; Enables or disables the circular reference collector.
; https://php.net/zend.enable-gc
zend.enable_gc = On

; If enabled, scripts may be written in encodings that are incompatible with
; the scanner.  CP936, Big5, CP949 and Shift_JIS are the examples of such
; encodings.  To use this feature, mbstring extension must be enabled.
;zend.multibyte = Off

; Allows to set the default encoding for the scripts.  This value will be used
; unless "declare(encoding=...)" directive appears at the top of the script.
; Only affects if zend.multibyte is set.
;zend.script_encoding =

; Allows to include or exclude arguments from stack traces generated for exceptions.
; In production, it is recommended to turn this setting on to prohibit the output
; of sensitive information in stack traces
; Default Value: Off
; Development Value: Off
; Production Value: On
zend.exception_ignore_args = On

; Allows setting the maximum string length in an argument of a stringified stack trace
; to a value between 0 and 1000000.
; This has no effect when zend.exception_ignore_args is enabled.
; Default Value: 15
; Development Value: 15
; Production Value: 0
; In production, it is recommended to set this to 0 to reduce the output
; of sensitive information in stack traces.
zend.exception_string_param_max_len = 0

;;;;;;;;;;;;;;;;;
; Miscellaneous ;
;;;;;;;;;;;;;;;;;

; Decides whether PHP may expose the fact that it is installed on the server
; (e.g. by adding its signature to the Web server header).  It is no security
; threat in any way, but it makes it possible to determine whether you use PHP
; on your server or not.
; https://php.net/expose-php
expose_php = Off

;;;;;;;;;;;;;;;;;;;
; Resource Limits ;
;;;;;;;;;;;;;;;;;;;

; Maximum execution time of each script, in seconds
; https://php.net/max-execution-time
; Note: This directive is hardcoded to 0 for the CLI SAPI
max_execution_time = 60

; Maximum amount of time each script may spend parsing request data. It's a good
; idea to limit this time on productions servers in order to eliminate unexpectedly
; long running scripts.
; Note: This directive is hardcoded to -1 for the CLI SAPI
; Default Value: -1 (Unlimited)
; Development Value: 60 (60 seconds)
; Production Value: 60 (60 seconds)
; https://php.net/max-input-time
max_input_time = 60

; Maximum input variable nesting level
; https://php.net/max-input-nesting-level
;max_input_nesting_level = 64

; How many GET/POST/COOKIE input variables may be accepted
;max_input_vars = 1000

; How many multipart body parts (combined input variable and file uploads) may
; be accepted.
; Default Value: -1 (Sum of max_input_vars and max_file_uploads)
;max_multipart_body_parts = 1500

; Maximum amount of memory a script may consume
; https://php.net/memory-limit
memory_limit = 500M

;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;
; Error handling and logging ;
;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;

; This directive informs PHP of which errors, warnings and notices you would like
; it to take action for. The recommended way of setting values for this
; directive is through the use of the error level constants and bitwise
; operators. The error level constants are below here for convenience as well as
; some common settings and their meanings.
; By default, PHP is set to take action on all errors, notices and warnings EXCEPT
; those related to E_NOTICE and E_STRICT, which together cover best practices and
; recommended coding standards in PHP. For performance reasons, this is the
; recommend error reporting setting. Your production server shouldn't be wasting
; resources complaining about best practices and coding standards. That's what
; development servers and development settings are for.
; Note: The php.ini-development file has this setting as E_ALL. This
; means it pretty much reports everything which is exactly what you want during
; development and early testing.
;
; Error Level Constants:
; E_ALL             - All errors and warnings
; E_ERROR           - fatal run-time errors
; E_RECOVERABLE_ERROR  - almost fatal run-time errors
; E_WARNING         - run-time warnings (non-fatal errors)
; E_PARSE           - compile-time parse errors
; E_NOTICE          - run-time notices (these are warnings which often result
;                     from a bug in your code, but it's possible that it was
;                     intentional (e.g., using an uninitialized variable and
;                     relying on the fact it is automatically initialized to an
;                     empty string)
; E_STRICT          - run-time notices, enable to have PHP suggest changes
;                     to your code which will ensure the best interoperability
;                     and forward compatibility of your code
; E_CORE_ERROR      - fatal errors that occur during PHP's initial startup
; E_CORE_WARNING    - warnings (non-fatal errors) that occur during PHP's
;                     initial startup
; E_COMPILE_ERROR   - fatal compile-time errors
; E_COMPILE_WARNING - compile-time warnings (non-fatal errors)
; E_USER_ERROR      - user-generated error message
; E_USER_WARNING    - user-generated warning message
; E_USER_NOTICE     - user-generated notice message
; E_DEPRECATED      - warn about code that will not work in future versions
;                     of PHP
; E_USER_DEPRECATED - user-generated deprecation warnings
;
; Common Values:
;   E_ALL (Show all errors, warnings and notices including coding standards.)
;   E_ALL & ~E_NOTICE  (Show all errors, except for notices)
;   E_ALL & ~E_NOTICE & ~E_STRICT  (Show all errors, except for notices and coding standards warnings.)
;   E_COMPILE_ERROR|E_RECOVERABLE_ERROR|E_ERROR|E_CORE_ERROR  (Show only errors)
; Default Value: E_ALL
; Development Value: E_ALL
; Production Value: E_ALL & ~E_DEPRECATED & ~E_STRICT
; https://php.net/error-reporting
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT

; This directive controls whether or not and where PHP will output errors,
; notices and warnings too. Error output is very useful during development, but
; it could be very dangerous in production environments. Depending on the code
; which is triggering the error, sensitive information could potentially leak
; out of your application such as database usernames and passwords or worse.
; For production environments, we recommend logging errors rather than
; sending them to STDOUT.
; Possible Values:
;   Off = Do not display any errors
;   stderr = Display errors to STDERR (affects only CGI/CLI binaries!)
;   On or stdout = Display errors to STDOUT
; Default Value: On
; Development Value: On
; Production Value: Off
; https://php.net/display-errors
display_errors = Off

; The display of errors which occur during PHP's startup sequence are handled
; separately from display_errors. We strongly recommend you set this to 'off'
; for production servers to avoid leaking configuration details.
; Default Value: On
; Development Value: On
; Production Value: Off
; https://php.net/display-startup-errors
display_startup_errors = Off

; Besides displaying errors, PHP can also log errors to locations such as a
; server-specific log, STDERR, or a location specified by the error_log
; directive found below. While errors should not be displayed on productions
; servers they should still be monitored and logging is a great way to do that.
; Default Value: Off
; Development Value: On
; Production Value: On
; https://php.net/log-errors
log_errors = On

; Do not log repeated messages. Repeated errors must occur in same file on same
; line unless ignore_repeated_source is set true.
; https://php.net/ignore-repeated-errors
ignore_repeated_errors = Off

; Ignore source of message when ignoring repeated messages. When this setting
; is On you will not log errors with repeated messages from different files or
; source lines.
; https://php.net/ignore-repeated-source
ignore_repeated_source = Off

; If this parameter is set to Off, then memory leaks will not be shown (on
; stdout or in the log). This is only effective in a debug compile, and if
; error reporting includes E_WARNING in the allowed list
; https://php.net/report-memleaks
report_memleaks = On

; This setting is off by default.
;report_zend_debug = 0

; Turn off normal error reporting and emit XML-RPC error XML
; https://php.net/xmlrpc-errors
;xmlrpc_errors = 0

; An XML-RPC faultCode
;xmlrpc_error_number = 0

; When PHP displays or logs an error, it has the capability of formatting the
; error message as HTML for easier reading. This directive controls whether
; the error message is formatted as HTML or not.
; Note: This directive is hardcoded to Off for the CLI SAPI
; https://php.net/html-errors
;html_errors = On

; If html_errors is set to On *and* docref_root is not empty, then PHP
; produces clickable error messages that direct to a page describing the error
; or function causing the error in detail.
; You can download a copy of the PHP manual from https://php.net/docs
; and change docref_root to the base URL of your local copy including the
; leading '/'. You must also specify the file extension being used including
; the dot. PHP's default behavior is to leave these settings empty, in which
; case no links to documentation are generated.
; Note: Never use this feature for production boxes.
; https://php.net/docref-root
; Examples
;docref_root = "/phpmanual/"

; https://php.net/docref-ext
;docref_ext = .html

; String to output before an error message. PHP's default behavior is to leave
; this setting blank.
; https://php.net/error-prepend-string
; Example:
;error_prepend_string = "<span style='color: #ff0000'>"

; String to output after an error message. PHP's default behavior is to leave
; this setting blank.
; https://php.net/error-append-string
; Example:
;error_append_string = "</span>"

; Log errors to specified file. PHP's default behavior is to leave this value
; empty.
; https://php.net/error-log
; Example:
;error_log = php_errors.log
; Log errors to syslog (Event Log on Windows).
;error_log = syslog

; The syslog ident is a string which is prepended to every message logged
; to syslog. Only used when error_log is set to syslog.
;syslog.ident = php

; The syslog facility is used to specify what type of program is logging
; the message. Only used when error_log is set to syslog.
;syslog.facility = user

; Set this to disable filtering control characters (the default).
; Some loggers only accept NVT-ASCII, others accept anything that's not
; control characters. If your logger accepts everything, then no filtering
; is needed at all.
; Allowed values are:
;   ascii (all printable ASCII characters and NL)
;   no-ctrl (all characters except control characters)
;   all (all characters)
;   raw (like "all", but messages are not split at newlines)
; https://php.net/syslog.filter
;syslog.filter = ascii

;windows.show_crt_warning
; Default value: 0
; Development value: 0
; Production value: 0

;;;;;;;;;;;;;;;;;
; Data Handling ;
;;;;;;;;;;;;;;;;;

; The separator used in PHP generated URLs to separate arguments.
; PHP's default setting is "&".
; https://php.net/arg-separator.output
; Example:
;arg_separator.output = "&amp;"

; List of separator(s) used by PHP to parse input URLs into variables.
; PHP's default setting is "&".
; NOTE: Every character in this directive is considered as separator!
; https://php.net/arg-separator.input
; Example:
;arg_separator.input = ";&"

; This directive determines which super global arrays are registered when PHP
; starts up. G,P,C,E & S are abbreviations for the following respective super
; globals: GET, POST, COOKIE, ENV and SERVER. There is a performance penalty
; paid for the registration of these arrays and because ENV is not as commonly
; used as the others, ENV is not recommended on productions servers. You
; can still get access to the environment variables through getenv() should you
; need to.
; Default Value: "EGPCS"
; Development Value: "GPCS"
; Production Value: "GPCS";
; https://php.net/variables-order
variables_order = "GPCS"

; This directive determines which super global data (G,P & C) should be
; registered into the super global array REQUEST. If so, it also determines
; the order in which that data is registered. The values for this directive
; are specified in the same manner as the variables_order directive,
; EXCEPT one. Leaving this value empty will cause PHP to use the value set
; in the variables_order directive. It does not mean it will leave the super
; globals array REQUEST empty.
; Default Value: None
; Development Value: "GP"
; Production Value: "GP"
; https://php.net/request-order
request_order = "GP"

; This directive determines whether PHP registers $argv & $argc each time it
; runs. $argv contains an array of all the arguments passed to PHP when a script
; is invoked. $argc contains an integer representing the number of arguments
; that were passed when the script was invoked. These arrays are extremely
; useful when running scripts from the command line. When this directive is
; enabled, registering these variables consumes CPU cycles and memory each time
; a script is executed. For performance reasons, this feature should be disabled
; on production servers.
; Note: This directive is hardcoded to On for the CLI SAPI
; Default Value: On
; Development Value: Off
; Production Value: Off
; https://php.net/register-argc-argv
register_argc_argv = Off

; When enabled, the ENV, REQUEST and SERVER variables are created when they're
; first used (Just In Time) instead of when the script starts. If these
; variables are not used within a script, having this directive on will result
; in a performance gain. The PHP directive register_argc_argv must be disabled
; for this directive to have any effect.
; https://php.net/auto-globals-jit
auto_globals_jit = On

; Whether PHP will read the POST data.
; This option is enabled by default.
; Most likely, you won't want to disable this option globally. It causes $_POST
; and $_FILES to always be empty; the only way you will be able to read the
; POST data will be through the php://input stream wrapper. This can be useful
; to proxy requests or to process the POST data in a memory efficient fashion.
; https://php.net/enable-post-data-reading
;enable_post_data_reading = Off

; Maximum size of POST data that PHP will accept.
; Its value may be 0 to disable the limit. It is ignored if POST data reading
; is disabled through enable_post_data_reading.
; https://php.net/post-max-size
post_max_size = 8M

; Automatically add files before PHP document.
; https://php.net/auto-prepend-file
auto_prepend_file =

; Automatically add files after PHP document.
; https://php.net/auto-append-file
auto_append_file =

; By default, PHP will output a media type using the Content-Type header. To
; disable this, simply set it to be empty.
;
; PHP's built-in default media type is set to text/html.
; https://php.net/default-mimetype
default_mimetype = "text/html"

; PHP's default character set is set to UTF-8.
; https://php.net/default-charset
default_charset = "UTF-8"

; PHP internal character encoding is set to empty.
; If empty, default_charset is used.
; https://php.net/internal-encoding
;internal_encoding =

; PHP input character encoding is set to empty.
; If empty, default_charset is used.
; https://php.net/input-encoding
;input_encoding =

; PHP output character encoding is set to empty.
; If empty, default_charset is used.
; See also output_buffer.
; https://php.net/output-encoding
;output_encoding =

;;;;;;;;;;;;;;;;;;;;;;;;;
; Paths and Directories ;
;;;;;;;;;;;;;;;;;;;;;;;;;

; UNIX: "/path1:/path2"
;include_path = ".:/usr/share/php"
;
; Windows: "\path1;\path2"
;include_path = ".;c:\php\includes"
;
; PHP's default setting for include_path is ".;/path/to/php/pear"
; https://php.net/include-path

; The root of the PHP pages, used only if nonempty.
; if PHP was not compiled with FORCE_REDIRECT, you SHOULD set doc_root
; if you are running php as a CGI under any web server (other than IIS)
; see documentation for security issues.  The alternate is to use the
; cgi.force_redirect configuration below
; https://php.net/doc-root
doc_root =

; The directory under which PHP opens the script using /~username used only
; if nonempty.
; https://php.net/user-dir
user_dir =

; Directory in which the loadable extensions (modules) reside.
; https://php.net/extension-dir
;extension_dir = "./"
; On windows:
;extension_dir = "ext"

; Directory where the temporary files should be placed.
; Defaults to the system default (see sys_get_temp_dir)
;sys_temp_dir = "/tmp"

; Whether or not to enable the dl() function.  The dl() function does NOT work
; properly in multithreaded servers, such as IIS or Zeus, and is automatically
; disabled on them.
; https://php.net/enable-dl
enable_dl = Off

; cgi.force_redirect is necessary to provide security running PHP as a CGI under
; most web servers.  Left undefined, PHP turns this on by default.  You can
; turn it off here AT YOUR OWN RISK
; **You CAN safely turn this off for IIS, in fact, you MUST.**
; https://php.net/cgi.force-redirect
;cgi.force_redirect = 1

; if cgi.nph is enabled it will force cgi to always sent Status: 200 with
; every request. PHP's default behavior is to disable this feature.
;cgi.nph = 1

; if cgi.force_redirect is turned on, and you are not running under Apache or Netscape
; (iPlanet) web servers, you MAY need to set an environment variable name that PHP
; will look for to know it is OK to continue execution.  Setting this variable MAY
; cause security issues, KNOW WHAT YOU ARE DOING FIRST.
; https://php.net/cgi.redirect-status-env
;cgi.redirect_status_env =

; cgi.fix_pathinfo provides *real* PATH_INFO/PATH_TRANSLATED support for CGI.  PHP's
; previous behaviour was to set PATH_TRANSLATED to SCRIPT_FILENAME, and to not grok
; what PATH_INFO is.  For more information on PATH_INFO, see the cgi specs.  Setting
; this to 1 will cause PHP CGI to fix its paths to conform to the spec.  A setting
; of zero causes PHP to behave as before.  Default is 1.  You should fix your scripts
; to use SCRIPT_FILENAME rather than PATH_TRANSLATED.
; https://php.net/cgi.fix-pathinfo
;cgi.fix_pathinfo=1

; if cgi.discard_path is enabled, the PHP CGI binary can safely be placed outside
; of the web tree and people will not be able to circumvent .htaccess security.
;cgi.discard_path=1

; FastCGI under IIS supports the ability to impersonate
; security tokens of the calling client.  This allows IIS to define the
; security context that the request runs under.  mod_fastcgi under Apache
; does not currently support this feature (03/17/2002)
; Set to 1 if running under IIS.  Default is zero.
; https://php.net/fastcgi.impersonate
;fastcgi.impersonate = 1

; Disable logging through FastCGI connection. PHP's default behavior is to enable
; this feature.
;fastcgi.logging = 0

; cgi.rfc2616_headers configuration option tells PHP what type of headers to
; use when sending HTTP response code. If set to 0, PHP sends Status: header that
; is supported by Apache. When this option is set to 1, PHP will send
; RFC2616 compliant header.
; Default is zero.
; https://php.net/cgi.rfc2616-headers
;cgi.rfc2616_headers = 0

; cgi.check_shebang_line controls whether CGI PHP checks for line starting with #!
; (shebang) at the top of the running script. This line might be needed if the
; script support running both as stand-alone script and via PHP CGI<. PHP in CGI
; mode skips this line and ignores its content if this directive is turned on.
; https://php.net/cgi.check-shebang-line
;cgi.check_shebang_line=1

;;;;;;;;;;;;;;;;
; File Uploads ;
;;;;;;;;;;;;;;;;

; Whether to allow HTTP file uploads.
; https://php.net/file-uploads
file_uploads = On

; Temporary directory for HTTP uploaded files (will use system default if not
; specified).
; https://php.net/upload-tmp-dir
;upload_tmp_dir =

; Maximum allowed size for uploaded files.
; https://php.net/upload-max-filesize
upload_max_filesize = 2M

; Maximum number of files that can be uploaded via a single request
max_file_uploads = 20

;;;;;;;;;;;;;;;;;;
; Fopen wrappers ;
;;;;;;;;;;;;;;;;;;

; Whether to allow the treatment of URLs (like http:// or ftp://) as files.
; https://php.net/allow-url-fopen
allow_url_fopen = On

; Whether to allow include/require to open URLs (like https:// or ftp://) as files.
; https://php.net/allow-url-include
allow_url_include = Off

; Define the anonymous ftp password (your email address). PHP's default setting
; for this is empty.
; https://php.net/from
;from="john@doe.com"

; Define the User-Agent string. PHP's default setting for this is empty.
; https://php.net/user-agent
;user_agent="PHP"

; Default timeout for socket based streams (seconds)
; https://php.net/default-socket-timeout
default_socket_timeout = 60

; If your scripts have to deal with files from Macintosh systems,
; or you are running on a Mac and need to deal with files from
; unix or win32 systems, setting this flag will cause PHP to
; automatically detect the EOL character in those files so that
; fgets() and file() will work regardless of the source of the file.
; https://php.net/auto-detect-line-endings
;auto_detect_line_endings = Off

;;;;;;;;;;;;;;;;;;;;;;
; Dynamic Extensions ;
;;;;;;;;;;;;;;;;;;;;;;

; If you wish to have an extension loaded automatically, use the following
; syntax:
;
;   extension=modulename
;
; For example:
;
;   extension=mysqli
;
; When the extension library to load is not located in the default extension
; directory, You may specify an absolute path to the library file:
;
;   extension=/path/to/extension/mysqli.so
;
; Note : The syntax used in previous PHP versions ('extension=<ext>.so' and
; 'extension='php_<ext>.dll') is supported for legacy reasons and may be
; deprecated in a future PHP major version. So, when it is possible, please
; move to the new ('extension=<ext>) syntax.
;
; Notes for Windows environments :
;
; - Many DLL files are located in the ext/
;   extension folders as well as the separate PECL DLL download.
;   Be sure to appropriately set the extension_dir directive.
;
;extension=bz2

; The ldap extension must be before curl if OpenSSL 1.0.2 and OpenLDAP is used
; otherwise it results in segfault when unloading after using SASL.
; See https://github.com/php/php-src/issues/8620 for more info.
;extension=ldap

;extension=curl
;extension=ffi
;extension=ftp
;extension=fileinfo
;extension=gd
;extension=gettext
;extension=gmp
;extension=intl
;extension=imap
;extension=mbstring
;extension=exif      ; Must be after mbstring as it depends on it
;extension=mysqli
;extension=oci8_12c  ; Use with Oracle Database 12c Instant Client
;extension=oci8_19  ; Use with Oracle Database 19 Instant Client
;extension=odbc
;extension=openssl
;extension=pdo_firebird
;extension=pdo_mysql
;extension=pdo_oci
;extension=pdo_odbc
;extension=pdo_pgsql
;extension=pdo_sqlite
;extension=pgsql
;extension=shmop

; The MIBS data available in the PHP distribution must be installed.
; See https://www.php.net/manual/en/snmp.installation.php
;extension=snmp

;extension=soap
;extension=sockets
;extension=sodium
;extension=sqlite3
;extension=tidy
;extension=xsl
;extension=zip

;zend_extension=opcache

;;;;;;;;;;;;;;;;;;;
; Module Settings ;
;;;;;;;;;;;;;;;;;;;

[CLI Server]
; Whether the CLI web server uses ANSI color coding in its terminal output.
cli_server.color = On

[Date]
; Defines the default timezone used by the date functions
; https://php.net/date.timezone
;date.timezone =

; https://php.net/date.default-latitude
;date.default_latitude = 31.7667

; https://php.net/date.default-longitude
;date.default_longitude = 35.2333

; https://php.net/date.sunrise-zenith
;date.sunrise_zenith = 90.833333

; https://php.net/date.sunset-zenith
;date.sunset_zenith = 90.833333

[filter]
; https://php.net/filter.default
;filter.default = unsafe_raw

; https://php.net/filter.default-flags
;filter.default_flags =

[iconv]
; Use of this INI entry is deprecated, use global input_encoding instead.
; If empty, default_charset or input_encoding or iconv.input_encoding is used.
; The precedence is: default_charset < input_encoding < iconv.input_encoding
;iconv.input_encoding =

; Use of this INI entry is deprecated, use global internal_encoding instead.
; If empty, default_charset or internal_encoding or iconv.internal_encoding is used.
; The precedence is: default_charset < internal_encoding < iconv.internal_encoding
;iconv.internal_encoding =

; Use of this INI entry is deprecated, use global output_encoding instead.
; If empty, default_charset or output_encoding or iconv.output_encoding is used.
; The precedence is: default_charset < output_encoding < iconv.output_encoding
; To use an output encoding conversion, iconv's output handler must be set
; otherwise output encoding conversion cannot be performed.
;iconv.output_encoding =

[imap]
; rsh/ssh logins are disabled by default. Use this INI entry if you want to
; enable them. Note that the IMAP library does not filter mailbox names before
; passing them to rsh/ssh command, thus passing untrusted data to this function
; with rsh/ssh enabled is insecure.
;imap.enable_insecure_rsh=0

[intl]
;intl.default_locale =
; This directive allows you to produce PHP errors when some error
; happens within intl functions. The value is the level of the error produced.
; Default is 0, which does not produce any errors.
;intl.error_level = E_WARNING
;intl.use_exceptions = 0

[sqlite3]
; Directory pointing to SQLite3 extensions
; https://php.net/sqlite3.extension-dir
;sqlite3.extension_dir =

; SQLite defensive mode flag (only available from SQLite 3.26+)
; When the defensive flag is enabled, language features that allow ordinary
; SQL to deliberately corrupt the database file are disabled. This forbids
; writing directly to the schema, shadow tables (eg. FTS data tables), or
; the sqlite_dbpage virtual table.
; https://www.sqlite.org/c3ref/c_dbconfig_defensive.html
; (for older SQLite versions, this flag has no use)
;sqlite3.defensive = 1

[Pcre]
; PCRE library backtracking limit.
; https://php.net/pcre.backtrack-limit
;pcre.backtrack_limit=100000

; PCRE library recursion limit.
; Please note that if you set this value to a high number you may consume all
; the available process stack and eventually crash PHP (due to reaching the
; stack size limit imposed by the Operating System).
; https://php.net/pcre.recursion-limit
;pcre.recursion_limit=100000

; Enables or disables JIT compilation of patterns. This requires the PCRE
; library to be compiled with JIT support.
;pcre.jit=1

[Pdo]
; Whether to pool ODBC connections. Can be one of "strict", "relaxed" or "off"
; https://php.net/pdo-odbc.connection-pooling
;pdo_odbc.connection_pooling=strict

[Pdo_mysql]
; Default socket name for local MySQL connects.  If empty, uses the built-in
; MySQL defaults.
pdo_mysql.default_socket=

[Phar]
; https://php.net/phar.readonly
;phar.readonly = On

; https://php.net/phar.require-hash
;phar.require_hash = On

;phar.cache_list =

[mail function]
; For Win32 only.
; https://php.net/smtp
SMTP = localhost
; https://php.net/smtp-port
smtp_port = 25

; For Win32 only.
; https://php.net/sendmail-from
;sendmail_from = me@example.com

; For Unix only.  You may supply arguments as well (default: "sendmail -t -i").
; https://php.net/sendmail-path
;sendmail_path =

; Force the addition of the specified parameters to be passed as extra parameters
; to the sendmail binary. These parameters will always replace the value of
; the 5th parameter to mail().
;mail.force_extra_parameters =

; Add X-PHP-Originating-Script: that will include uid of the script followed by the filename
mail.add_x_header = Off

; Use mixed LF and CRLF line separators to keep compatibility with some
; RFC 2822 non conformant MTA.
mail.mixed_lf_and_crlf = Off

; The path to a log file that will log all mail() calls. Log entries include
; the full path of the script, line number, To address and headers.
;mail.log =
; Log mail to syslog (Event Log on Windows).
;mail.log = syslog

[ODBC]
; https://php.net/odbc.default-db
;odbc.default_db    =  Not yet implemented

; https://php.net/odbc.default-user
;odbc.default_user  =  Not yet implemented

; https://php.net/odbc.default-pw
;odbc.default_pw    =  Not yet implemented

; Controls the ODBC cursor model.
; Default: SQL_CURSOR_STATIC (default).
;odbc.default_cursortype

; Allow or prevent persistent links.
; https://php.net/odbc.allow-persistent
odbc.allow_persistent = On

; Check that a connection is still valid before reuse.
; https://php.net/odbc.check-persistent
odbc.check_persistent = On

; Maximum number of persistent links.  -1 means no limit.
; https://php.net/odbc.max-persistent
odbc.max_persistent = -1

; Maximum number of links (persistent + non-persistent).  -1 means no limit.
; https://php.net/odbc.max-links
odbc.max_links = -1

; Handling of LONG fields.  Returns number of bytes to variables.  0 means
; passthru.
; https://php.net/odbc.defaultlrl
odbc.defaultlrl = 4096

; Handling of binary data.  0 means passthru, 1 return as is, 2 convert to char.
; See the documentation on odbc_binmode and odbc_longreadlen for an explanation
; of odbc.defaultlrl and odbc.defaultbinmode
; https://php.net/odbc.defaultbinmode
odbc.defaultbinmode = 1

[MySQLi]

; Maximum number of persistent links.  -1 means no limit.
; https://php.net/mysqli.max-persistent
mysqli.max_persistent = -1

; Allow accessing, from PHP's perspective, local files with LOAD DATA statements
; https://php.net/mysqli.allow_local_infile
;mysqli.allow_local_infile = On

; It allows the user to specify a folder where files that can be sent via LOAD DATA
; LOCAL can exist. It is ignored if mysqli.allow_local_infile is enabled.
;mysqli.local_infile_directory =

; Allow or prevent persistent links.
; https://php.net/mysqli.allow-persistent
mysqli.allow_persistent = On

; Maximum number of links.  -1 means no limit.
; https://php.net/mysqli.max-links
mysqli.max_links = -1

; Default port number for mysqli_connect().  If unset, mysqli_connect() will use
; the $MYSQL_TCP_PORT or the mysql-tcp entry in /etc/services or the
; compile-time value defined MYSQL_PORT (in that order).  Win32 will only look
; at MYSQL_PORT.
; https://php.net/mysqli.default-port
mysqli.default_port = 3306

; Default socket name for local MySQL connects.  If empty, uses the built-in
; MySQL defaults.
; https://php.net/mysqli.default-socket
mysqli.default_socket =

; Default host for mysqli_connect() (doesn't apply in safe mode).
; https://php.net/mysqli.default-host
mysqli.default_host =

; Default user for mysqli_connect() (doesn't apply in safe mode).
; https://php.net/mysqli.default-user
mysqli.default_user =

; Default password for mysqli_connect() (doesn't apply in safe mode).
; Note that this is generally a *bad* idea to store passwords in this file.
; *Any* user with PHP access can run 'echo get_cfg_var("mysqli.default_pw")
; and reveal this password!  And of course, any users with read access to this
; file will be able to reveal the password as well.
; https://php.net/mysqli.default-pw
mysqli.default_pw =

; Allow or prevent reconnect
mysqli.reconnect = Off

; If this option is enabled, closing a persistent connection will rollback
; any pending transactions of this connection, before it is put back
; into the persistent connection pool.
;mysqli.rollback_on_cached_plink = Off

[mysqlnd]
; Enable / Disable collection of general statistics by mysqlnd which can be
; used to tune and monitor MySQL operations.
mysqlnd.collect_statistics = On

; Enable / Disable collection of memory usage statistics by mysqlnd which can be
; used to tune and monitor MySQL operations.
mysqlnd.collect_memory_statistics = Off

; Records communication from all extensions using mysqlnd to the specified log
; file.
; https://php.net/mysqlnd.debug
;mysqlnd.debug =

; Defines which queries will be logged.
;mysqlnd.log_mask = 0

; Default size of the mysqlnd memory pool, which is used by result sets.
;mysqlnd.mempool_default_size = 16000

; Size of a pre-allocated buffer used when sending commands to MySQL in bytes.
;mysqlnd.net_cmd_buffer_size = 2048

; Size of a pre-allocated buffer used for reading data sent by the server in
; bytes.
;mysqlnd.net_read_buffer_size = 32768

; Timeout for network requests in seconds.
;mysqlnd.net_read_timeout = 31536000

; SHA-256 Authentication Plugin related. File with the MySQL server public RSA
; key.
;mysqlnd.sha256_server_public_key =

[OCI8]

; Connection: Enables privileged connections using external
; credentials (OCI_SYSOPER, OCI_SYSDBA)
; https://php.net/oci8.privileged-connect
;oci8.privileged_connect = Off

; Connection: The maximum number of persistent OCI8 connections per
; process. Using -1 means no limit.
; https://php.net/oci8.max-persistent
;oci8.max_persistent = -1

; Connection: The maximum number of seconds a process is allowed to
; maintain an idle persistent connection. Using -1 means idle
; persistent connections will be maintained forever.
; https://php.net/oci8.persistent-timeout
;oci8.persistent_timeout = -1

; Connection: The number of seconds that must pass before issuing a
; ping during oci_pconnect() to check the connection validity. When
; set to 0, each oci_pconnect() will cause a ping. Using -1 disables
; pings completely.
; https://php.net/oci8.ping-interval
;oci8.ping_interval = 60

; Connection: Set this to a user chosen connection class to be used
; for all pooled server requests with Oracle Database Resident
; Connection Pooling (DRCP).  To use DRCP, this value should be set to
; the same string for all web servers running the same application,
; the database pool must be configured, and the connection string must
; specify to use a pooled server.
;oci8.connection_class =

; High Availability: Using On lets PHP receive Fast Application
; Notification (FAN) events generated when a database node fails. The
; database must also be configured to post FAN events.
;oci8.events = Off

; Tuning: This option enables statement caching, and specifies how
; many statements to cache. Using 0 disables statement caching.
; https://php.net/oci8.statement-cache-size
;oci8.statement_cache_size = 20

; Tuning: Enables row prefetching and sets the default number of
; rows that will be fetched automatically after statement execution.
; https://php.net/oci8.default-prefetch
;oci8.default_prefetch = 100

; Tuning: Sets the amount of LOB data that is internally returned from
; Oracle Database when an Oracle LOB locator is initially retrieved as
; part of a query. Setting this can improve performance by reducing
; round-trips.
; https://php.net/oci8.prefetch-lob-size
; oci8.prefetch_lob_size = 0

; Compatibility. Using On means oci_close() will not close
; oci_connect() and oci_new_connect() connections.
; https://php.net/oci8.old-oci-close-semantics
;oci8.old_oci_close_semantics = Off

[PostgreSQL]
; Allow or prevent persistent links.
; https://php.net/pgsql.allow-persistent
pgsql.allow_persistent = On

; Detect broken persistent links always with pg_pconnect().
; Auto reset feature requires a little overheads.
; https://php.net/pgsql.auto-reset-persistent
pgsql.auto_reset_persistent = Off

; Maximum number of persistent links.  -1 means no limit.
; https://php.net/pgsql.max-persistent
pgsql.max_persistent = -1

; Maximum number of links (persistent+non persistent).  -1 means no limit.
; https://php.net/pgsql.max-links
pgsql.max_links = -1

; Ignore PostgreSQL backends Notice message or not.
; Notice message logging require a little overheads.
; https://php.net/pgsql.ignore-notice
pgsql.ignore_notice = 0

; Log PostgreSQL backends Notice message or not.
; Unless pgsql.ignore_notice=0, module cannot log notice message.
; https://php.net/pgsql.log-notice
pgsql.log_notice = 0

[bcmath]
; Number of decimal digits for all bcmath functions.
; https://php.net/bcmath.scale
bcmath.scale = 0

[browscap]
; https://php.net/browscap
;browscap = extra/browscap.ini

[Session]
; Handler used to store/retrieve data.
; https://php.net/session.save-handler
session.save_handler = files

; Argument passed to save_handler.  In the case of files, this is the path
; where data files are stored. Note: Windows users have to change this
; variable in order to use PHP's session functions.
;
; The path can be defined as:
;
;     session.save_path = "N;/path"
;
; where N is an integer.  Instead of storing all the session files in
; /path, what this will do is use subdirectories N-levels deep, and
; store the session data in those directories.  This is useful if
; your OS has problems with many files in one directory, and is
; a more efficient layout for servers that handle many sessions.
;
; NOTE 1: PHP will not create this directory structure automatically.
;         You can use the script in the ext/session dir for that purpose.
; NOTE 2: See the section on garbage collection below if you choose to
;         use subdirectories for session storage
;
; The file storage module creates files using mode 600 by default.
; You can change that by using
;
;     session.save_path = "N;MODE;/path"
;
; where MODE is the octal representation of the mode. Note that this
; does not overwrite the process's umask.
; https://php.net/session.save-path
;session.save_path = "/var/lib/php/sessions"

; Whether to use strict session mode.
; Strict session mode does not accept an uninitialized session ID, and
; regenerates the session ID if the browser sends an uninitialized session ID.
; Strict mode protects applications from session fixation via a session adoption
; vulnerability. It is disabled by default for maximum compatibility, but
; enabling it is encouraged.
; https://wiki.php.net/rfc/strict_sessions
session.use_strict_mode = 0

; Whether to use cookies.
; https://php.net/session.use-cookies
session.use_cookies = 1

; https://php.net/session.cookie-secure
;session.cookie_secure =

; This option forces PHP to fetch and use a cookie for storing and maintaining
; the session id. We encourage this operation as it's very helpful in combating
; session hijacking when not specifying and managing your own session id. It is
; not the be-all and end-all of session hijacking defense, but it's a good start.
; https://php.net/session.use-only-cookies
session.use_only_cookies = 1

; Name of the session (used as cookie name).
; https://php.net/session.name
session.name = PHPSESSID

; Initialize session on request startup.
; https://php.net/session.auto-start
session.auto_start = 0

; Lifetime in seconds of cookie or, if 0, until browser is restarted.
; https://php.net/session.cookie-lifetime
session.cookie_lifetime = 0

; The path for which the cookie is valid.
; https://php.net/session.cookie-path
session.cookie_path = /

; The domain for which the cookie is valid.
; https://php.net/session.cookie-domain
session.cookie_domain =

; Whether or not to add the httpOnly flag to the cookie, which makes it
; inaccessible to browser scripting languages such as JavaScript.
; https://php.net/session.cookie-httponly
session.cookie_httponly =

; Add SameSite attribute to cookie to help mitigate Cross-Site Request Forgery (CSRF/XSRF)
; Current valid values are "Strict", "Lax" or "None". When using "None",
; make sure to include the quotes, as `none` is interpreted like `false` in ini files.
; https://tools.ietf.org/html/draft-west-first-party-cookies-07
session.cookie_samesite =

; Handler used to serialize data. php is the standard serializer of PHP.
; https://php.net/session.serialize-handler
session.serialize_handler = php

; Defines the probability that the 'garbage collection' process is started on every
; session initialization. The probability is calculated by using gc_probability/gc_divisor,
; e.g. 1/100 means there is a 1% chance that the GC process starts on each request.
; Default Value: 1
; Development Value: 1
; Production Value: 1
; https://php.net/session.gc-probability
session.gc_probability = 0

; Defines the probability that the 'garbage collection' process is started on every
; session initialization. The probability is calculated by using gc_probability/gc_divisor,
; e.g. 1/100 means there is a 1% chance that the GC process starts on each request.
; For high volume production servers, using a value of 1000 is a more efficient approach.
; Default Value: 100
; Development Value: 1000
; Production Value: 1000
; https://php.net/session.gc-divisor
session.gc_divisor = 1000

; After this number of seconds, stored data will be seen as 'garbage' and
; cleaned up by the garbage collection process.
; https://php.net/session.gc-maxlifetime
session.gc_maxlifetime = 1440

; NOTE: If you are using the subdirectory option for storing session files
;       (see session.save_path above), then garbage collection does *not*
;       happen automatically.  You will need to do your own garbage
;       collection through a shell script, cron entry, or some other method.
;       For example, the following script is the equivalent of setting
;       session.gc_maxlifetime to 1440 (1440 seconds = 24 minutes):
;          find /path/to/sessions -cmin +24 -type f | xargs rm

; Check HTTP Referer to invalidate externally stored URLs containing ids.
; HTTP_REFERER has to contain this substring for the session to be
; considered as valid.
; https://php.net/session.referer-check
session.referer_check =

; Set to {nocache,private,public,} to determine HTTP caching aspects
; or leave this empty to avoid sending anti-caching headers.
; https://php.net/session.cache-limiter
session.cache_limiter = nocache

; Document expires after n minutes.
; https://php.net/session.cache-expire
session.cache_expire = 180

; trans sid support is disabled by default.
; Use of trans sid may risk your users' security.
; Use this option with caution.
; - User may send URL contains active session ID
;   to other person via. email/irc/etc.
; - URL that contains active session ID may be stored
;   in publicly accessible computer.
; - User may access your site with the same session ID
;   always using URL stored in browser's history or bookmarks.
; https://php.net/session.use-trans-sid
session.use_trans_sid = 0

; Set session ID character length. This value could be between 22 to 256.
; Shorter length than default is supported only for compatibility reason.
; Users should use 32 or more chars.
; https://php.net/session.sid-length
; Default Value: 32
; Development Value: 26
; Production Value: 26
session.sid_length = 26

; The URL rewriter will look for URLs in a defined set of HTML tags.
; <form> is special; if you include them here, the rewriter will
; add a hidden <input> field with the info which is otherwise appended
; to URLs. <form> tag's action attribute URL will not be modified
; unless it is specified.
; Note that all valid entries require a "=", even if no value follows.
; Default Value: "a=href,area=href,frame=src,form="
; Development Value: "a=href,area=href,frame=src,form="
; Production Value: "a=href,area=href,frame=src,form="
; https://php.net/url-rewriter.tags
session.trans_sid_tags = "a=href,area=href,frame=src,form="

; URL rewriter does not rewrite absolute URLs by default.
; To enable rewrites for absolute paths, target hosts must be specified
; at RUNTIME. i.e. use ini_set()
; <form> tags is special. PHP will check action attribute's URL regardless
; of session.trans_sid_tags setting.
; If no host is defined, HTTP_HOST will be used for allowed host.
; Example value: php.net,www.php.net,wiki.php.net
; Use "," for multiple hosts. No spaces are allowed.
; Default Value: ""
; Development Value: ""
; Production Value: ""
;session.trans_sid_hosts=""

; Define how many bits are stored in each character when converting
; the binary hash data to something readable.
; Possible values:
;   4  (4 bits: 0-9, a-f)
;   5  (5 bits: 0-9, a-v)
;   6  (6 bits: 0-9, a-z, A-Z, "-", ",")
; Default Value: 4
; Development Value: 5
; Production Value: 5
; https://php.net/session.hash-bits-per-character
session.sid_bits_per_character = 5

; Enable upload progress tracking in $_SESSION
; Default Value: On
; Development Value: On
; Production Value: On
; https://php.net/session.upload-progress.enabled
;session.upload_progress.enabled = On

; Cleanup the progress information as soon as all POST data has been read
; (i.e. upload completed).
; Default Value: On
; Development Value: On
; Production Value: On
; https://php.net/session.upload-progress.cleanup
;session.upload_progress.cleanup = On

; A prefix used for the upload progress key in $_SESSION
; Default Value: "upload_progress_"
; Development Value: "upload_progress_"
; Production Value: "upload_progress_"
; https://php.net/session.upload-progress.prefix
;session.upload_progress.prefix = "upload_progress_"

; The index name (concatenated with the prefix) in $_SESSION
; containing the upload progress information
; Default Value: "PHP_SESSION_UPLOAD_PROGRESS"
; Development Value: "PHP_SESSION_UPLOAD_PROGRESS"
; Production Value: "PHP_SESSION_UPLOAD_PROGRESS"
; https://php.net/session.upload-progress.name
;session.upload_progress.name = "PHP_SESSION_UPLOAD_PROGRESS"

; How frequently the upload progress should be updated.
; Given either in percentages (per-file), or in bytes
; Default Value: "1%"
; Development Value: "1%"
; Production Value: "1%"
; https://php.net/session.upload-progress.freq
;session.upload_progress.freq =  "1%"

; The minimum delay between updates, in seconds
; Default Value: 1
; Development Value: 1
; Production Value: 1
; https://php.net/session.upload-progress.min-freq
;session.upload_progress.min_freq = "1"

; Only write session data when session data is changed. Enabled by default.
; https://php.net/session.lazy-write
;session.lazy_write = On

[Assertion]
; Switch whether to compile assertions at all (to have no overhead at run-time)
; -1: Do not compile at all
;  0: Jump over assertion at run-time
;  1: Execute assertions
; Changing from or to a negative value is only possible in php.ini! (For turning assertions on and off at run-time, see assert.active, when zend.assertions = 1)
; Default Value: 1
; Development Value: 1
; Production Value: -1
; https://php.net/zend.assertions
zend.assertions = -1

; Assert(expr); active by default.
; https://php.net/assert.active
;assert.active = On

; Throw an AssertionError on failed assertions
; https://php.net/assert.exception
;assert.exception = On

; Issue a PHP warning for each failed assertion. (Overridden by assert.exception if active)
; https://php.net/assert.warning
;assert.warning = On

; Don't bail out by default.
; https://php.net/assert.bail
;assert.bail = Off

; User-function to be called if an assertion fails.
; https://php.net/assert.callback
;assert.callback = 0

[COM]
; path to a file containing GUIDs, IIDs or filenames of files with TypeLibs
; https://php.net/com.typelib-file
;com.typelib_file =

; allow Distributed-COM calls
; https://php.net/com.allow-dcom
;com.allow_dcom = true

; autoregister constants of a component's typelib on com_load()
; https://php.net/com.autoregister-typelib
;com.autoregister_typelib = true

; register constants casesensitive
; https://php.net/com.autoregister-casesensitive
;com.autoregister_casesensitive = false

; show warnings on duplicate constant registrations
; https://php.net/com.autoregister-verbose
;com.autoregister_verbose = true

; The default character set code-page to use when passing strings to and from COM objects.
; Default: system ANSI code page
;com.code_page=

; The version of the .NET framework to use. The value of the setting are the first three parts
; of the framework's version number, separated by dots, and prefixed with "v", e.g. "v4.0.30319".
;com.dotnet_version=

[mbstring]
; language for internal character representation.
; This affects mb_send_mail() and mbstring.detect_order.
; https://php.net/mbstring.language
;mbstring.language = Japanese

; Use of this INI entry is deprecated, use global internal_encoding instead.
; internal/script encoding.
; Some encoding cannot work as internal encoding. (e.g. SJIS, BIG5, ISO-2022-*)
; If empty, default_charset or internal_encoding or iconv.internal_encoding is used.
; The precedence is: default_charset < internal_encoding < iconv.internal_encoding
;mbstring.internal_encoding =

; Use of this INI entry is deprecated, use global input_encoding instead.
; http input encoding.
; mbstring.encoding_translation = On is needed to use this setting.
; If empty, default_charset or input_encoding or mbstring.input is used.
; The precedence is: default_charset < input_encoding < mbstring.http_input
; https://php.net/mbstring.http-input
;mbstring.http_input =

; Use of this INI entry is deprecated, use global output_encoding instead.
; http output encoding.
; mb_output_handler must be registered as output buffer to function.
; If empty, default_charset or output_encoding or mbstring.http_output is used.
; The precedence is: default_charset < output_encoding < mbstring.http_output
; To use an output encoding conversion, mbstring's output handler must be set
; otherwise output encoding conversion cannot be performed.
; https://php.net/mbstring.http-output
;mbstring.http_output =

; enable automatic encoding translation according to
; mbstring.internal_encoding setting. Input chars are
; converted to internal encoding by setting this to On.
; Note: Do _not_ use automatic encoding translation for
;       portable libs/applications.
; https://php.net/mbstring.encoding-translation
;mbstring.encoding_translation = Off

; automatic encoding detection order.
; "auto" detect order is changed according to mbstring.language
; https://php.net/mbstring.detect-order
;mbstring.detect_order = auto

; substitute_character used when character cannot be converted
; one from another
; https://php.net/mbstring.substitute-character
;mbstring.substitute_character = none

; Enable strict encoding detection.
;mbstring.strict_detection = Off

; This directive specifies the regex pattern of content types for which mb_output_handler()
; is activated.
; Default: mbstring.http_output_conv_mimetypes=^(text/|application/xhtml\+xml)
;mbstring.http_output_conv_mimetypes=

; This directive specifies maximum stack depth for mbstring regular expressions. It is similar
; to the pcre.recursion_limit for PCRE.
;mbstring.regex_stack_limit=100000

; This directive specifies maximum retry count for mbstring regular expressions. It is similar
; to the pcre.backtrack_limit for PCRE.
;mbstring.regex_retry_limit=1000000

[gd]
; Tell the jpeg decode to ignore warnings and try to create
; a gd image. The warning will then be displayed as notices
; disabled by default
; https://php.net/gd.jpeg-ignore-warning
;gd.jpeg_ignore_warning = 1

[exif]
; Exif UNICODE user comments are handled as UCS-2BE/UCS-2LE and JIS as JIS.
; With mbstring support this will automatically be converted into the encoding
; given by corresponding encode setting. When empty mbstring.internal_encoding
; is used. For the decode settings you can distinguish between motorola and
; intel byte order. A decode setting cannot be empty.
; https://php.net/exif.encode-unicode
;exif.encode_unicode = ISO-8859-15

; https://php.net/exif.decode-unicode-motorola
;exif.decode_unicode_motorola = UCS-2BE

; https://php.net/exif.decode-unicode-intel
;exif.decode_unicode_intel    = UCS-2LE

; https://php.net/exif.encode-jis
;exif.encode_jis =

; https://php.net/exif.decode-jis-motorola
;exif.decode_jis_motorola = JIS

; https://php.net/exif.decode-jis-intel
;exif.decode_jis_intel    = JIS

[Tidy]
; The path to a default tidy configuration file to use when using tidy
; https://php.net/tidy.default-config
;tidy.default_config = /usr/local/lib/php/default.tcfg

; Should tidy clean and repair output automatically?
; WARNING: Do not use this option if you are generating non-html content
; such as dynamic images
; https://php.net/tidy.clean-output
tidy.clean_output = Off

[soap]
; Enables or disables WSDL caching feature.
; https://php.net/soap.wsdl-cache-enabled
soap.wsdl_cache_enabled=1

; Sets the directory name where SOAP extension will put cache files.
; https://php.net/soap.wsdl-cache-dir
soap.wsdl_cache_dir="/tmp"

; (time to live) Sets the number of second while cached file will be used
; instead of original one.
; https://php.net/soap.wsdl-cache-ttl
soap.wsdl_cache_ttl=86400

; Sets the size of the cache limit. (Max. number of WSDL files to cache)
soap.wsdl_cache_limit = 5

[sysvshm]
; A default size of the shared memory segment
;sysvshm.init_mem = 10000

[ldap]
; Sets the maximum number of open links or -1 for unlimited.
ldap.max_links = -1

[dba]
;dba.default_handler=

[opcache]
; Determines if Zend OPCache is enabled
;opcache.enable=1

; Determines if Zend OPCache is enabled for the CLI version of PHP
;opcache.enable_cli=0

; The OPcache shared memory storage size.
;opcache.memory_consumption=128

; The amount of memory for interned strings in Mbytes.
;opcache.interned_strings_buffer=8

; The maximum number of keys (scripts) in the OPcache hash table.
; Only numbers between 200 and 1000000 are allowed.
;opcache.max_accelerated_files=10000

; The maximum percentage of "wasted" memory until a restart is scheduled.
;opcache.max_wasted_percentage=5

; When this directive is enabled, the OPcache appends the current working
; directory to the script key, thus eliminating possible collisions between
; files with the same name (basename). Disabling the directive improves
; performance, but may break existing applications.
;opcache.use_cwd=1

; When disabled, you must reset the OPcache manually or restart the
; webserver for changes to the filesystem to take effect.
;opcache.validate_timestamps=1

; How often (in seconds) to check file timestamps for changes to the shared
; memory storage allocation. ("1" means validate once per second, but only
; once per request. "0" means always validate)
;opcache.revalidate_freq=2

; Enables or disables file search in include_path optimization
;opcache.revalidate_path=0

; If disabled, all PHPDoc comments are dropped from the code to reduce the
; size of the optimized code.
;opcache.save_comments=1

; If enabled, compilation warnings (including notices and deprecations) will
; be recorded and replayed each time a file is included. Otherwise, compilation
; warnings will only be emitted when the file is first cached.
;opcache.record_warnings=0

; Allow file existence override (file_exists, etc.) performance feature.
;opcache.enable_file_override=0

; A bitmask, where each bit enables or disables the appropriate OPcache
; passes
;opcache.optimization_level=0x7FFFBFFF

;opcache.dups_fix=0

; The location of the OPcache blacklist file (wildcards allowed).
; Each OPcache blacklist file is a text file that holds the names of files
; that should not be accelerated. The file format is to add each filename
; to a new line. The filename may be a full path or just a file prefix
; (i.e., /var/www/x  blacklists all the files and directories in /var/www
; that start with 'x'). Line starting with a ; are ignored (comments).
;opcache.blacklist_filename=

; Allows exclusion of large files from being cached. By default all files
; are cached.
;opcache.max_file_size=0

; Check the cache checksum each N requests.
; The default value of "0" means that the checks are disabled.
;opcache.consistency_checks=0

; How long to wait (in seconds) for a scheduled restart to begin if the cache
; is not being accessed.
;opcache.force_restart_timeout=180

; OPcache error_log file name. Empty string assumes "stderr".
;opcache.error_log=

; All OPcache errors go to the Web server log.
; By default, only fatal errors (level 0) or errors (level 1) are logged.
; You can also enable warnings (level 2), info messages (level 3) or
; debug messages (level 4).
;opcache.log_verbosity_level=1

; Preferred Shared Memory back-end. Leave empty and let the system decide.
;opcache.preferred_memory_model=

; Protect the shared memory from unexpected writing during script execution.
; Useful for internal debugging only.
;opcache.protect_memory=0

; Allows calling OPcache API functions only from PHP scripts which path is
; started from specified string. The default "" means no restriction
;opcache.restrict_api=

; Mapping base of shared memory segments (for Windows only). All the PHP
; processes have to map shared memory into the same address space. This
; directive allows to manually fix the "Unable to reattach to base address"
; errors.
;opcache.mmap_base=

; Facilitates multiple OPcache instances per user (for Windows only). All PHP
; processes with the same cache ID and user share an OPcache instance.
;opcache.cache_id=

; Enables and sets the second level cache directory.
; It should improve performance when SHM memory is full, at server restart or
; SHM reset. The default "" disables file based caching.
;opcache.file_cache=

; Enables or disables opcode caching in shared memory.
;opcache.file_cache_only=0

; Enables or disables checksum validation when script loaded from file cache.
;opcache.file_cache_consistency_checks=1

; Implies opcache.file_cache_only=1 for a certain process that failed to
; reattach to the shared memory (for Windows only). Explicitly enabled file
; cache is required.
;opcache.file_cache_fallback=1

; Enables or disables copying of PHP code (text segment) into HUGE PAGES.
; Under certain circumstances (if only a single global PHP process is
; started from which all others fork), this can increase performance
; by a tiny amount because TLB misses are reduced.  On the other hand, this
; delays PHP startup, increases memory usage and degrades performance
; under memory pressure - use with care.
; Requires appropriate OS configuration.
;opcache.huge_code_pages=0

; Validate cached file permissions.
;opcache.validate_permission=0

; Prevent name collisions in chroot'ed environment.
;opcache.validate_root=0

; If specified, it produces opcode dumps for debugging different stages of
; optimizations.
;opcache.opt_debug_level=0

; Specifies a PHP script that is going to be compiled and executed at server
; start-up.
; https://php.net/opcache.preload
;opcache.preload=

; Preloading code as root is not allowed for security reasons. This directive
; facilitates to let the preloading to be run as another user.
; https://php.net/opcache.preload_user
;opcache.preload_user=

; Prevents caching files that are less than this number of seconds old. It
; protects from caching of incompletely updated files. In case all file updates
; on your site are atomic, you may increase performance by setting it to "0".
;opcache.file_update_protection=2

; Absolute path used to store shared lockfiles (for *nix only).
;opcache.lockfile_path=/tmp

[curl]
; A default value for the CURLOPT_CAINFO option. This is required to be an
; absolute path.
;curl.cainfo =

[openssl]
; The location of a Certificate Authority (CA) file on the local filesystem
; to use when verifying the identity of SSL/TLS peers. Most users should
; not specify a value for this directive as PHP will attempt to use the
; OS-managed cert stores in its absence. If specified, this value may still
; be overridden on a per-stream basis via the "cafile" SSL stream context
; option.
;openssl.cafile=

; If openssl.cafile is not specified or if the CA file is not found, the
; directory pointed to by openssl.capath is searched for a suitable
; certificate. This value must be a correctly hashed certificate directory.
; Most users should not specify a value for this directive as PHP will
; attempt to use the OS-managed cert stores in its absence. If specified,
; this value may still be overridden on a per-stream basis via the "capath"
; SSL stream context option.
;openssl.capath=

[ffi]
; FFI API restriction. Possible values:
; "preload" - enabled in CLI scripts and preloaded files (default)
; "false"   - always disabled
; "true"    - always enabled
;ffi.enable=preload

; List of headers files to preload, wildcard patterns allowed.
;ffi.preload=
INI;

    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    private function phpVersion (): string
    {
        if (empty($this->version)) {
            $this->runCommand(function ($out) { $this->version = $out; }, null, "bash", "-c", <<<EOF
php -r 'echo PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION;'
EOF,
            );
        }
        return trim($this->version);
    }
}