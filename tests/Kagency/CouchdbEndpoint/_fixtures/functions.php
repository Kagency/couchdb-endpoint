<?php

/**
 * Execute a HTTP request
 *
 * Will throw an exception if the server responds with a status >= 400 or on
 * connection failure.
 *
 * Returns the response body on success as a string.
 *
 * @param array $host
 * @param string $path
 * @param string $method
 * @param mixed $data
 * @return string
 */
function http_request(array $host, $path, $method = 'GET', $data = null, $contentType = 'application/json')
{
    $httpFilePointer = @fopen(
        $url = $host['scheme'] . '://' . $host['user'] . ':' . $host['pass'] . '@' . $host['host']  . ':' . $host['port'] . $path,
        'r',
        false,
        stream_context_create(
            array(
                'http' => array(
                    'method'        => $method,
                    'content'       => $data,
                    'ignore_errors' => true,
                    'header'        => 'Content-type: ' . $contentType,
                ),
            )
        )
    );

    // Check if connection has been established successfully
    if ( $httpFilePointer === false )
    {
        $error = error_get_last();
        throw new Exception( "Could not connect to server at $url: $error" );
    }

    // Read request body
    $body = '';
    while ( !feof( $httpFilePointer ) )
    {
        $body .= fgets( $httpFilePointer );
    }

    $metaData   = stream_get_meta_data( $httpFilePointer );
    // This depends on wheather PHP is installed with curl stream wrappers or
    // not…
    $rawHeaders = isset( $metaData['wrapper_data']['headers'] ) ? $metaData['wrapper_data']['headers'] : $metaData['wrapper_data'];
    $headers    = array();
    foreach ( $rawHeaders as $lineContent )
    {
        // Extract header values
        if ( preg_match( '(^HTTP/(?P<version>\d+\.\d+)\s+(?P<status>\d+))S', $lineContent, $match ) )
        {
            $headers['version'] = $match['version'];
            $headers['status']  = (int) $match['status'];
        }
        else
        {
            list( $key, $value ) = explode( ':', $lineContent, 2 );
            $headers[strtolower( $key )] = ltrim( $value );
        }
    }

    // @HACK: Ugly hack. Detect auth fail by matching agaisnt the location
    // header. CouchDB answers with 200 OK on failed, but required, auth.
    if ( isset( $headers['location'] ) &&
         preg_match( '(/session.*reason=(?P<reason>.*)$)', $headers['location'], $match ) )
    {
        throw new Exception( "Auth failure: " . urldecode( $match['reason'] ) );
    }

    if ( $headers['status'] >= 400 )
    {
        throw new Exception( "Server returned with error: " . $headers['status'] . " for URL $url\n\n" . $body );
    }

    return $body;
}
