<?php

namespace etobi\extensionUtils\Command\Ter;

use etobi\extensionUtils\Command\AbstractCommand;
use etobi\extensionUtils\Controller\TerController;

use etobi\extensionUtils\Service\ExtensionsXml;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use etobi\extensionUtils\Service\Filesystem;

/**
 * UpdateInfoCommand updates the extension information
 *
 * @author Christian Zenker <christian.zenker@599media.de>
 */
class UpdateInfoCommand extends AbstractCommand
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ter:update-info')
            ->setDefinition(array())
            ->setDescription('Update local extension information cache')
            ->setHelp(<<<EOT
Update the locally cached extension information.

It is recommended that you update this regularly.
If you can't find the latest version of an extension, it is most likely that the
extension information cache is outdated.
EOT
)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $url = 'http://typo3.org/fileadmin/ter/extensions.xml.gz';
        $extensionsXmlFile = ExtensionsXml::getDefaultFilename();
        $extensionsXmlFileGzipped = $extensionsXmlFile . '.gz';

        $this->logger->info('fetch extension info from "' . $url .'"');

        $callback = $this->getProgressCallback();
        $downloader = new \etobi\extensionUtils\Service\Downloader();
        $downloader->downloadFile($url, $extensionsXmlFileGzipped, $callback);

        $this->logger->info(sprintf('unpacking "%s"...', $extensionsXmlFileGzipped));

        $gzip = new Filesystem();
        $gzipReturn = $gzip->unzip($extensionsXmlFileGzipped, $extensionsXmlFile);
        if($gzipReturn) {
            $this->logger->notice('extension info updated');
        } else {
            $this->logger->critical('extension info was not updated');
        }

        $fs = new \Symfony\Component\Filesystem\Filesystem();
        $fs->chmod($extensionsXmlFileGzipped, 0666);
        $fs->chmod($extensionsXmlFile, 0666);
    }
}
