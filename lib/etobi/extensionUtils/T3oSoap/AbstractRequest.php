<?php

namespace etobi\extensionUtils\T3oSoap;
use etobi\extensionUtils\T3oSoap\Exception\AccessDeniedException;
use etobi\extensionUtils\T3oSoap\Exception\ExtensionKeyNotExistsException;
use etobi\extensionUtils\T3oSoap\Exception\NoUserOrPasswordException;
use etobi\extensionUtils\T3oSoap\Exception\SoapServerError;
use etobi\extensionUtils\T3oSoap\Exception\Typo3VersionIncorrectException;
use etobi\extensionUtils\T3oSoap\Exception\UserNotFoundException;
use etobi\extensionUtils\T3oSoap\Exception\WrongPasswordException;

/**
 * a request object that queries the TYPO3.org SOAP API
 */
abstract class AbstractRequest {

    const TX_TER_ERROR_GENERAL_EXTREPDIRDOESNTEXIST = '100';
    const TX_TER_ERROR_GENERAL_NOUSERORPASSWORD = '101';
    const TX_TER_ERROR_GENERAL_USERNOTFOUND = '102';
    const TX_TER_ERROR_GENERAL_WRONGPASSWORD = '103';
    const TX_TER_ERROR_GENERAL_DATABASEERROR = '104';

    const TX_TER_ERROR_UPLOADEXTENSION_EXTENSIONDOESNTEXIST = '202';
    const TX_TER_ERROR_UPLOADEXTENSION_EXTENSIONCONTAINSNOFILES = '203';
    const TX_TER_ERROR_UPLOADEXTENSION_WRITEERRORWHILEWRITINGFILES = '204';
    const TX_TER_ERROR_UPLOADEXTENSION_EXTENSIONTOOBIG = '205';
    const TX_TER_ERROR_UPLOADEXTENSION_EXISTINGEXTENSIONRECORDNOTFOUND = '206';
    const TX_TER_ERROR_UPLOADEXTENSION_FILEMD5DOESNOTMATCH = '207';
    const TX_TER_ERROR_UPLOADEXTENSION_ACCESSDENIED = '208';

    const TX_TER_ERROR_REGISTEREXTENSIONKEY_DBERRORWHILEINSERTINGKEY = '300';

    const TX_TER_ERROR_DELETEEXTENSIONKEY_ACCESSDENIED = '500';
    const TX_TER_ERROR_DELETEEXTENSIONKEY_KEYDOESNOTEXIST = '501';
    const TX_TER_ERROR_DELETEEXTENSIONKEY_CANTDELETEBECAUSEVERSIONSEXIST = '502';

    const TX_TER_ERROR_MODIFYEXTENSIONKEY_ACCESSDENIED = '600';
    const TX_TER_ERROR_MODIFYEXTENSIONKEY_SETTINGTOTHISOWNERISNOTPOSSIBLE = '601';
    const TX_TER_ERROR_MODIFYEXTENSIONKEY_KEYDOESNOTEXIST = '602';

    const TX_TER_ERROR_SETREVIEWSTATE_NOUSERGROUPDEFINED = '700';
    const TX_TER_ERROR_SETREVIEWSTATE_ACCESSDENIED = '701';
    const TX_TER_ERROR_SETREVIEWSTATE_EXTENSIONVERSIONDOESNOTEXIST = '702';

    const TX_TER_ERROR_INCREASEEXTENSIONDOWNLOADCOUNTER_NOUSERGROUPDEFINED = '800';
    const TX_TER_ERROR_INCREASEEXTENSIONDOWNLOADCOUNTER_ACCESSDENIED = '801';
    const TX_TER_ERROR_INCREASEEXTENSIONDOWNLOADCOUNTER_EXTENSIONVERSIONDOESNOTEXIST = '802';
    const TX_TER_ERROR_INCREASEEXTENSIONDOWNLOADCOUNTER_INCREMENTORNOTPOSITIVEINTEGER = '803';
    const TX_TER_ERROR_INCREASEEXTENSIONDOWNLOADCOUNTER_EXTENSIONKEYDOESNOTEXIST = '804';

    const TX_TER_ERROR_DELETEEXTENSION_ACCESS_DENIED = '900';
    const TX_TER_ERROR_DELETEEXTENSION_EXTENSIONDOESNTEXIST = '901';

    const TX_TER_RESULT_GENERAL_OK = '10000';
    const TX_TER_RESULT_ERRORS_OCCURRED = '10001';

    const TX_TER_RESULT_EXTENSIONKEYALREADYEXISTS = '10500';
    const TX_TER_RESULT_EXTENSIONKEYDOESNOTEXIST = '10501';
    const TX_TER_RESULT_EXTENSIONKEYNOTVALID = '10502';
    const TX_TER_RESULT_EXTENSIONKEYSUCCESSFULLYREGISTERED = '10503';
    const TX_TER_RESULT_EXTENSIONSUCCESSFULLYUPLOADED = '10504';
    const TX_TER_RESULT_EXTENSIONSUCCESSFULLYDELETED = '10505';

	const TX_TER_ERROR_UPLOADEXTENSION_TYPO3DEPENDENCYINCORRECT = '209';
	const TX_TER_ERROR_UPLOADEXTENSION_TYPO3DEPENDENCYCHECKFAILED = '210';
    
    /**
     * @var string
     */
    protected $wsdlURL = 'http://www.typo3.org/wsdl/tx_ter_wsdl.php';

    /**
     * @var null|Client
     */
    protected $client = NULL;

    /**
     * @param string $wsdlURL
     */
    public function setWsdlURL($wsdlURL)
    {
        $this->wsdlURL = $wsdlURL;
    }

    /**
     * @return string
     */
    public function getWsdlURL()
    {
        return $this->wsdlURL;
    }

    /**
     * @param \etobi\extensionUtils\T3oSoap\Client|null $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    /**
     * @return \etobi\extensionUtils\T3oSoap\Client|null
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * create a new client
     *
     * @return Client
     */
    protected function createClient() {
        $this->client = new Client($this->wsdlURL);
        return $this->client;
    }

	/**
	 * handle a generic \SoapFault and throw an according Exception object
	 * @param \SoapFault $e
	 * @return \Exception
	 */
	protected function convertSoapError(\SoapFault $e) {
		if($e->faultcode == self::TX_TER_ERROR_GENERAL_DATABASEERROR ||
			$e->faultcode == self::TX_TER_ERROR_UPLOADEXTENSION_TYPO3DEPENDENCYCHECKFAILED
		) {
			return new SoapServerError($e->getMessage(), $e->getCode());
		}
		if($e->faultcode == self::TX_TER_ERROR_GENERAL_NOUSERORPASSWORD) {
			return new NoUserOrPasswordException($e->getMessage(), $e->getCode());
		}
		if($e->faultcode == self::TX_TER_ERROR_GENERAL_WRONGPASSWORD) {
			return new WrongPasswordException($e->getMessage(), $e->getCode());
		}
		if($e->faultcode == self::TX_TER_ERROR_GENERAL_USERNOTFOUND) {
			return new UserNotFoundException($e->getMessage(), $e->getCode());
		}
		if($e->faultcode == self::TX_TER_ERROR_MODIFYEXTENSIONKEY_ACCESSDENIED ||
			$e->faultcode == self::TX_TER_ERROR_DELETEEXTENSIONKEY_ACCESSDENIED ||
			$e->faultcode == self::TX_TER_ERROR_UPLOADEXTENSION_ACCESSDENIED
		) {
			return new AccessDeniedException($e->getMessage(), $e->getCode());
		}
		if($e->faultcode == self::TX_TER_ERROR_UPLOADEXTENSION_EXTENSIONDOESNTEXIST) {
			return new ExtensionKeyNotExistsException($e->getMessage(), $e->getCode());
		}
		if($e->faultcode == self::TX_TER_ERROR_UPLOADEXTENSION_TYPO3DEPENDENCYINCORRECT) {
			return new Typo3VersionIncorrectException($e->getMessage(), $e->getCode());
		}
		return $e;
	}
}