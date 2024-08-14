<?php

namespace App\State\Processor;

use App\Entity\User;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Polyfill\Mbstring\Mbstring;

/**
 * Processor class providing customized handling
 * of User entity CRUD operations hooked on top 
 * of built-in Doctrine persistance logic.
 * 
 * @implements ProcessorInterface<User, User|void>
 */
final class UserProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        #[Autowire(service: 'api_platform.doctrine.orm.state.remove_processor')]
        private ProcessorInterface $removeProcessor
    ) {
    }

    /**
     * Customized User entity processing logic extending 
     * built-in Doctrine's CRUD perstistance logic
     * via Processor interface process() method
     * 
     * @return User|void
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($operation instanceof DeleteOperationInterface) {
            // deleting the entity
            return $this->removeProcessor->process($data, $operation, $uriVariables, $context);
        }

    
        // ================================================= //
        // ================== PRE-PERSIST ================== //

        // make sure that ID of the resource CAN NOT be changed
        // through the update calls via PUT or PATCH requests
        $this->_preventUserIdModificationOnUpdate($data, $operation, $uriVariables);

        // perform multibyte trim above all string-typed
        // user entity attributes values
        $this->_trimTextualAttributes($data);

        // ================== PRE-PERSIST ================== //
        // ================================================= //


        // persisting the entity
        $result = $this->persistProcessor->process($data, $operation, $uriVariables, $context);


        // ================================================= //
        // ================= POST-PERSIST ================== //


        // ================= POST-PERSIST ================== //
        // ================================================= //

    
        return $result;
    }

    /**
     * Method preventing modification of user ID
     * during the PATCH/PUT API update requests.
     * 
     * @param \App\Entity\User $user
     * @param \ApiPlatform\Metadata\Operation $operation
     * @param array $uriVariables
     * @return void
     * @throws BadRequestHttpException
     */
    private function _preventUserIdModificationOnUpdate(User $user, Operation $operation, array $uriVariables = []): void {
        // make sure that ID of the resource CAN NOT be changed
        // through the update calls via PUT or PATCH requests
        if ($operation instanceof Put || $operation instanceof Patch) {
            // original user ID identifying of user resource
            // passed over via called API URI
            // (e.g. /api/users/<UUID-of-modified-user>)
            $userIdFromUri       = (string) $uriVariables['id'];
            // possibly modified user entity ID from PATCH/PUT JSON body
            $userIdFromJsonBody  = (string) $user->getId();

            // raise BAD REQUEST exception if attempt to modify the user ID
            // via the PUT/PATCH methods JSON body content has been detected
            if($userIdFromUri !== $userIdFromJsonBody) {
                throw new BadRequestHttpException('Modification of resource identifier value refused.');
            }
        }
    }

    /**
     * Perform multibyte trim above textual attributes of User entity
     * 
     * @param \App\Entity\User $user
     * @return void
     */
    private function _trimTextualAttributes(User $user): void
    {
        // get the list of string-typed
        // user entity attributes
        // TODO - pick this info from entity manager
        // @link https://stackoverflow.com/questions/27293233/how-to-get-the-type-of-a-doctrine-entity-property
        // @link https://stackoverflow.com/questions/44809739/is-there-a-way-to-inject-entitymanager-into-a-service
        $textualFields = [
            'name',
            'surname',
            'email',
            'note'
        ];

        // run multibyte trim above non-NULL valued
        // textual fields of provided user entity instance
        foreach ($textualFields as $textualFieldName) {
            $getterName = 'get' . ucfirst($textualFieldName);
            $fieldValue = $user->$getterName();
            if (!is_null($fieldValue)) {
                $setterName = 'set' . ucfirst($textualFieldName);
                $user->$setterName(Mbstring::mb_trim($fieldValue));
            }
        }
    }
}