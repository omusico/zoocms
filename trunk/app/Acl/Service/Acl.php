<?php

/**
 * Access Control List service for permissions check
 * @package    Acl
 * @subpackage Service
 */

/**
 * @package    Acl
 * @subpackage Service
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Acl_Service_Acl extends Zoo_Service {
    protected $service = null;
    /**
     * Get instance of the service object
     *
     * @return Zend_Acl
     */
    function &getService($config = null) {
        if (is_null($this->service)) {

            try {
                $this->service = Zoo::getService("cache")->getCache('acl', 'Core')->load('acl');
            }
            catch (Zoo_Exception_Service $e) {
                // Cache unavailable
            }

            if (!($this->service)) {
                $this->service = new Zend_Acl();
                $this->fetchAcl();
            }
        }
        return $this;
    }

    /**
     * Route calls to nondefined methods to the service
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments) {
        return call_user_func_array(array($this->service, $name), $arguments);
    }

    /**
     * Fetch - and cache - ACL rules
     *
     */
    function fetchAcl() {
        // Add roles to ACL
        $factory = new Acl_Role_Factory();
        $roles = $factory->fetchAll();

        $roletree = new Zoo_Object_Tree($roles, 'id', 'parent');
        $this->addRolesFromTree($roletree);

        // Add resources to ACL
        $factory = new Acl_Resource_Factory();
        $resources = $factory->fetchAll();

        $resourcetree = new Zoo_Object_Tree($resources, 'id', 'parent');
        $this->addResourcesFromTree($resourcetree);

        // Add rules to ACL
        $factory = new Acl_Rule_Factory();
        $rules = $factory->fetchAll();
        foreach ($rules as $rule) {
            $role = $roletree->getByKey($rule->roleid);
            $resource = $resourcetree->getByKey($rule->resourceid);
            $privileges = null;
            if ($rule->privilege != "") {
                $privileges = $rule->privilege;
                if ($rule->type != "") {
                    $privileges .= ".".$rule->type;
                }
            }
            $this->service->allow($role, $resource, $privileges);
        }
        try {
            Zoo::getService("cache")->getCache('acl')->save($this->service, "acl");
        }
        catch (Zoo_Exception_Service $e) {
            // Cache unavailable
        }
    }

    /**
     * Add roles to ACL from a Zoo_Object_Tree
     *
     * @param Zoo_Object_Tree $tree
     */
    function addRolesFromTree(Zoo_Object_Tree $tree, $id = 0) {
        foreach ($tree->getFirstChild($id) as $role) {
            if ($id > 0) {
                $this->service->addRole($role);
            }
            else {
                $this->service->addRole($role, $tree->getByKey($role->parent));
            }
            $this->addRolesFromTree($tree, $role->id);
        }
    }

    /**
     * Add resources to ACL from a Zoo_Object_Tree
     *
     * @param Zoo_Object_Tree $tree
     */
    function addResourcesFromTree(Zoo_Object_Tree $tree, $id = 0) {
        foreach ($tree->getFirstChild($id) as $role) {
            if ($role->parent == 0) {
                $this->service->add($role);
            }
            else {
                $this->service->add($role, $tree->getByKey($role->parent));
            }
            $this->addResourcesFromTree($tree, $role->id);
        }
    }

    /**
     * Get groups for a user
     * @param int $id
     * @return array
     */
    function getGroups($id) {
        $groups = array();
        $role_factory = new Acl_Role_Factory();
        if ($id > 0) {
            // Fetch groups from database
            $userRole_factory = new Acl_Role_User_Factory();
            $userRoles = $userRole_factory->fetchAll($userRole_factory->select()->where("uid = ?", $id));

            // Fetch roles from database, if user roles defined
            if (count($userRoles) > 0) {
                $roleIds = array();
                foreach ($userRoles as $userRole) {
                    $roleIds[] = $userRole->rid;
                }

                $roles = $role_factory->fetchAll($role_factory->select()->where("id IN (?)", $roleIds));
                foreach ($roles as $role) {
                    $groups[$role->id] = $role;
                }
            }
        }
        if ($groups == array() ) {
            // If id == 0 OR still no groups connected to the user, assign guest role
            $groups[0] = $role_factory->createRow(array('id' => 0, 'name' => 'guest', 'parent' => 0));
        }
        return $groups;
    }
    
/**
     * Check access to performing actions on content
     *
     * @param Zoo_Content_Interface $item
     * @param string $privilege
     * @return bool
     */
    function checkItemAccess(Zoo_Content_Interface $item, $privilege = "index") {
        try {
        	$user = Zoo::getService('user')->getCurrentUser();
            if ($privilege == "editown") {
                if ($item->uid != $user->id) {
                    return false;
                }
            }
			if ($this->checkAccess($privilege, $user) ||
                $this->checkAccess($privilege.'.'.$item->type, $user)) {
                // Explicit true/false returning for better readability
                return true;
            }
        }
        catch (Zend_Acl_Exception $e) {
            // Most likely reason: Resource doesn't exist - should we do something? It will deny access...

            // Log?
        }
        return false;
    }
    
    /**
     * Check access for a user for a given privilege on a resource
     * 
     * @param $user
     * @param $privilege
     * @param $resource
     * @return bool
     */
    function checkAccess($privilege, $user = null, $resource = "content.node") {
    	if ($user == null) {
    		$user = Zoo::getService('user')->getCurrentUser();
    	}
    	$roles = $user->getGroups();
        foreach ($roles as $role) {
            if ($this->isAllowed($role, $resource, $privilege)) {
                return true;
            }
        }
    }
}