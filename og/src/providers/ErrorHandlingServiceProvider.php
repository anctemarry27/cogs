<?php namespace Og\Providers;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */
class ErrorHandlingServiceProvider extends ServiceProvider
{
    /**
     * @param        $error_number
     * @param        $message
     * @param string $file
     * @param int    $line
     * @param array  $context
     *
     * @return bool
     */
    public function handle_error($error_number, $message, $file = '', $line = 0, $context = [])
    {
        $readable_error = readable_error_type($error_number);

        switch ($error_number)
        {
            case E_NOTICE:
            case E_USER_NOTICE:
                dump(compact('error_number', 'readable_error', 'message', 'file', 'line', 'context'));
                return FALSE;
                break;

            case E_USER_ERROR:
                dump(compact('error_number', 'readable_error', 'message', 'file', 'line', 'context'));
                break;

            case E_WARNING:
            case E_USER_WARNING:
                dump(compact('error_number', 'readable_error', 'message', 'file', 'line', 'context'));

                return FALSE;
                break;

            default :
                dump(compact('error_number', 'readable_error', 'message', 'file', 'line', 'context'));
                break;
        }

        return TRUE;
    }

    /**
     * @param \Exception|\Throwable $exception
     */
    public function handle_exception($exception)
    {
        if (getenv('APP_ENV') === 'dev')
        {
            list($code, $file, $line, $message, $previous, $trace, $trace_string) = [
                $exception->getCode(),
                $exception->getFile(),
                $exception->getLine(),
                $exception->getMessage(),
                $exception->getPrevious(),
                $exception->getTrace(),
                $exception->getTraceAsString(),
            ];

            $trace_info = "<b>file</b>: {$trace[0]['file']} <b>in line</b> ({$trace[0]['line']})";

            echo "<h2>COGS Runtime Exception: [::$code] $message</h2>";
            echo "<b>Trace:</b><br>";
            echo "<pre>$trace_string</pre>";
            echo "<b>Debug:</b><br>";

            dump(compact('code', 'file', 'line', 'message', 'previous', 'trace'));
        }
    }

    public function handle_shutdown()
    {
        dlog('[' . elapsed_time() . '] application::shutdown', 'debug');
    }

    /**
     * Register the handlers.
     */
    public function register()
    {
        error_reporting(getenv('APP_ENV') === 'dev' ? E_ALL : 0);

        set_error_handler([$this, 'handle_error']);
        set_exception_handler([$this, 'handle_exception']);
        register_shutdown_function([$this, 'handle_shutdown']);
    }
}
