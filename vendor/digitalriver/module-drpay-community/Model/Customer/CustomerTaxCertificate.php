<?php

namespace Digitalriver\DrPay\Model\Customer;

use Digitalriver\DrPay\Api\DigitalRiverCustomerIdManagementInterface;
use Digitalriver\DrPay\Helper\Drapi;
use Magento\Customer\Model\Session;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Digitalriver\DrPay\Model\DigitalRiverCustomer;
use Magento\Framework\Serialize\Serializer\Json;

class CustomerTaxCertificate
{
    private const VALID_FILETYPES = [ "jpg", "pdf", "png", "csv"];

    /**
     * @param Drapi $drApi
     * @param Session $session
     * @param DigitalRiverCustomerIdManagementInterface $drCustomerId
     */

    /**
     * @var \Digitalriver\DrPay\Logger\Logger
     */
    private $logger;

    /**
     * @var Json
     */
    private $jsonSerializer;


    public function __construct(
        Drapi                                     $drApi,
        Session                                   $session,
        DigitalRiverCustomerIdManagementInterface $drCustomerId,
        Filesystem                                $filesystem,
        UploaderFactory                           $uploaderFactory,
        CustomerRepositoryInterface               $customerRepository,
        DigitalRiverCustomer                      $digitalRiverCustomer,
        \Digitalriver\DrPay\Logger\Logger         $logger,
        Json $jsonSerializer,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->customerRepository = $customerRepository;
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->drApi = $drApi;
        $this->session = $session;
        $this->drCustomerId = $drCustomerId;
        $this->digitalRiverCustomer = $digitalRiverCustomer;
        $this->logger = $logger;
        $this->jsonSerializer = $jsonSerializer;
        $this->storeManager = $storeManager; 
    }

    /**
     * @return false|mixed
     */
    public function getDrCustomerTaxCertificate()
    {
        $customer = $this->session->getCustomerData();
        $currentStoreId = $this->storeManager->getStore()->getId();
        $storeId = $customer->getStoreId();
        $drCustomerId = $this->digitalRiverCustomer->getDrCustomerId($customer);
        $this->session->setCurrentCustomerStoreId($currentStoreId);
        $customerResponse = $this->drApi->getCustomer($drCustomerId);
        return $customerResponse['message']['taxCertificates'] ?? false;
    }

    /**
     * @param $data
     * @param $file
     * @return array|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveCustomerCertificate($data, $file)
    {
        $drCustomerId = $this->digitalRiverCustomer->getDrCustomerId();
        $fileId = $this->saveCertificate($file);

        if (empty($fileId)) {
            return false;
        }

        $certificateData = [
            "companyName" => $data["company"],
            "taxAuthority" => $data["tax_authority"],
            "startDate" => $this->formatDate($data["start_date"]),
            "endDate" => $this->formatDate($data["expiry_date"]),
            "fileId" => $fileId
        ];

        return $this->drApi->addCertificate($drCustomerId, $certificateData);
    }

    /**
     * @param string $date
     * @return string
     */
    public function formatDate(string $date): string
    {
        return date_format(date_create($date), 'Y-m-d\TH:i:s\Z');
    }

    /**
     * @param $file
     * @return array|bool|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function saveCertificate($file)
    {
        $uploadData = $file->get('upload_cert');
        if ($uploadData === null || empty($uploadData["name"])) {
            return false;
        }

        try {
            /** @var \Magento\MediaStorage\Model\File\Uploader $uploader */
            $uploader = $this->uploaderFactory->create(['fileId' => 'upload_cert']);
            $uploader->setAllowedExtensions(self::VALID_FILETYPES);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $varDirectory = $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR);
            $destinationPath = $varDirectory->getAbsolutePath('upload_cert');

            if (!$uploader->checkAllowedExtension($uploader->getFileExtension())) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __(
                        'Invalid file type. Provided: %1, Supported: %2',
                        $uploader->getFileExtension(),
                        implode(', ', self::VALID_FILETYPES)
                    )
                );
            }

            $result = $uploader->save($destinationPath);

            if (!$result) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Digital River certificate file cannot be saved to path: %1', $destinationPath)
                );
            }

            return $this->drApi->uploadTaxCertificate($destinationPath . $result['file']);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            // Allow localized exceptions to bubble up.
            throw $e;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Digital River certificate file cannot be saved. An unexpected error occurred.'),
                $e
            );
        }
    }
}
