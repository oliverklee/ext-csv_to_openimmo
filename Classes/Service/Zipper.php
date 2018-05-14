<?php
namespace OliverKlee\CsvToOpenImmo\Service;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class takes care of finding, reading and writing ZIP files.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Zipper implements SingletonInterface
{
    /**
     * @var string
     */
    const EXTRACTION_DIRECTORY_IN_TEMP = 'csv_to_openimmo/';

    /**
     * @var string
     */
    private $extractionDirectory = '';

    /**
     * @var string
     */
    private $sourceDirectory = '';

    public function __construct()
    {
        $this->extractionDirectory = PATH_site . 'typo3temp/' . self::EXTRACTION_DIRECTORY_IN_TEMP;
    }

    /**
     * @return string absolute path within typo3temp
     */
    public function getExtractionDirectory()
    {
        return $this->extractionDirectory;
    }

    /**
     * This method should be used only in the tests.
     *
     * @param string $directory
     *
     * @return void
     */
    public function setExtractionDirectory($directory)
    {
        $this->extractionDirectory = $directory;
    }

    /**
     * @param string $directory
     *
     * @return void
     */
    public function setSourceDirectory($directory)
    {
        $this->sourceDirectory = $directory;
    }

    /**
     * @return string[]
     *
     * @throws \BadMethodCallException
     */
    public function getPathsOfZipsToExtract()
    {
        if ($this->sourceDirectory === '') {
            throw new \BadMethodCallException('Please set a source directory first.', 1526320458);
        }

        return GeneralUtility::getAllFilesAndFoldersInPath([], $this->sourceDirectory, 'zip');
    }

    /**
     * @param string $relativeZipPath file path relative to the source directory
     *
     * @return string the absolute path of the folder with the extracted contents
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function extractZip($relativeZipPath)
    {
        $fullZipPath = rtrim($this->sourceDirectory, '/') . '/' . $relativeZipPath;
        $this->checkZipPath($fullZipPath);

        $zip = new \ZipArchive();
        if (!$zip->open($fullZipPath)) {
            throw new \RuntimeException('The ZIP file "' . $fullZipPath . '" could not be extracted.', 1526408289);
        }

        $extractionDirectory = $this->createExtractionFolder($fullZipPath);
        $zip->extractTo($extractionDirectory);
        $zip->close();

        return $extractionDirectory;
    }

    /**
     * @param string $path
     *
     * @throws \InvalidArgumentException
     */
    private function checkZipPath($path)
    {
        if (strtolower(substr($path, -4)) !== '.zip') {
            throw new \InvalidArgumentException('The file "' . $path . '" is no ZIP file.', 1526408098);
        }
        if (!file_exists($path)) {
            throw new \InvalidArgumentException('The ZIP file "' . $path . '" does not exist.', 1526407786);
        }
        if (!is_readable($path)) {
            throw new \InvalidArgumentException(
                'The ZIP file "' . $path . '" exists, but is not readable.',
                1526407809
            );
        }
    }

    /**
     * Creates a folder to extract a ZIP archive to (if it does not exist yet).
     *
     * @param string $pathOfZip path of a ZIP archive to get the folders name, must not be empty
     *
     * @return string full path for folder named like the ZIP archive without the suffix '.zip'
     */
    private function createExtractionFolder($pathOfZip)
    {
        $folder = $this->getNameForExtractionFolder($pathOfZip);
        if (!is_dir($folder)) {
            GeneralUtility::mkdir($folder);
        }

        return $folder;
    }

    /**
     * Gets a name for a folder according to the ZIP archive to extract to it.
     *
     * @param string $pathOfZip path of a ZIP archive, must not be empty
     *
     * @return string path for a folder named like the ZIP archive, empty if the passed string is empty
     */
    private function getNameForExtractionFolder($pathOfZip)
    {
        $relativeDirectoryName = str_ireplace('.zip', '/', basename($pathOfZip));

        return $this->extractionDirectory . '/' . $relativeDirectoryName;
    }
}
