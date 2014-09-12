<?php

namespace etobi\extensionUtils\Service;

/**
 * a wrapper to gzip and ungzip a file
 */
class Filesystem {

    protected $bin = 'gzip';

    /**
     * unzip a file
     *
     * The source file will be deleted
     *
     * @param $source
     * @param $destination
     * @return bool
     * @throws \RuntimeException
     */
    public function unzip($source, $destination) {
        if(function_exists('gzopen')) {
            $ih = gzopen($source, 'r');
            if($ih) {
                $oh = fopen($destination, 'w+');
                if($oh) {
                    while(!gzeof($ih)) {
                        $data = gzread($ih, 10240);
                        fwrite($oh, $data);
                    }
                    fclose($oh);
                }
                gzclose($ih);
            }
        }
        else {
            $cmd = $this->createGzipCommand($source, $destination, '-df');
            $returnCode = 0;
            system($cmd, $returnCode);
            if($returnCode !== 0) {
                throw new \RuntimeException(sprintf('The command "%s" exit with code %d.', $cmd, $returnCode));
            }
            return file_exists($destination);
        }
    }

    /**
     * @param string $source
     * @param string $destination
     * @param string $flags
     * @return string
     */
    protected function createGzipCommand($source, $destination, $flags = '') {
        return sprintf(
            '%s %s %s > %s',
            $this->bin,
            $flags,
            escapeshellarg($source),
            escapeshellarg($destination)
        );
    }
}