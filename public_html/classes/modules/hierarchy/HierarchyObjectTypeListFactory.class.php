<?php
/**********************************************************************************
 * This file is part of "FairnessTNA", a Payroll and Time Management program.
 * FairnessTNA is copyright 2013-2017 Aydan Coskun (aydan.ayfer.coskun@gmail.com)
 * others. For full attribution and copyrights details see the COPYRIGHT file.
 *
 * FairnessTNA is free software; you can redistribute it and/or modify it under the
 * terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation, either version 3 of the License, or (at you option )
 * any later version.
 *
 * FairnessTNA is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along
 * with this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *********************************************************************************/


/**
 * @package Modules\Hierarchy
 */
class HierarchyObjectTypeListFactory extends HierarchyObjectTypeFactory implements IteratorAggregate
{
    public function getAll($limit = null, $page = null, $where = null, $order = null)
    {
        $query = '
					select	*
					from	' . $this->getTable() . '
				';
        $query .= $this->getWhereSQL($where);
        $query .= $this->getSortSQL($order);

        $this->ExecuteSQL($query, null, $limit, $page);

        return $this;
    }

    public function getById($id, $where = null, $order = null)
    {
        if ($id == '') {
            return false;
        }

        $ph = array(
            'id' => (int)$id,
        );


        $query = '
					select	*
					from	' . $this->getTable() . '
					where	id = ?
				';
        $query .= $this->getWhereSQL($where);
        $query .= $this->getSortSQL($order);

        $this->ExecuteSQL($query, $ph);

        return $this;
    }

    public function getByHierarchyControlId($id, $where = null, $order = null)
    {
        if ($id == '') {
            return false;
        }

        $ph = array(
            'id' => (int)$id,
        );


        $query = '
					select	*
					from	' . $this->getTable() . '
					where	hierarchy_control_id = ?
				';
        $query .= $this->getWhereSQL($where);
        $query .= $this->getSortSQL($order);

        $this->ExecuteSQL($query, $ph);

        return $this;
    }

    public function getByCompanyIdAndObjectTypeId($id, $object_type_id, $where = null, $order = null)
    {
        if ($id == '') {
            return false;
        }

        if ($object_type_id == '') {
            return false;
        }

        $strict_order = true;
        if ($order == null) {
            //$order = array('b.last_name' => 'asc');
            $strict_order = false;
        }

        $cache_id = $id . $object_type_id;

        $hcf = new HierarchyControlFactory();
        $hotf = new HierarchyObjectTypeFactory();

        $this->rs = $this->getCache($cache_id);
        if ($this->rs === false) {
            $ph = array(
                'id' => (int)$id,
                'object_type_id' => (int)$object_type_id,
            );

            $query = '
						select	*
						from	' . $this->getTable() . ' as a,
								' . $hcf->getTable() . ' as b,
								' . $hotf->getTable() . ' as c

						where	a.hierarchy_control_id = b.id
							AND a.hierarchy_control_id = c.hierarchy_control_id
							AND b.company_id = ?
							AND c.object_type_id = ?
							AND b.deleted = 0
					';
            $query .= $this->getWhereSQL($where);
            $query .= $this->getSortSQL($order, $strict_order);

            $this->ExecuteSQL($query, $ph);

            $this->saveCache($this->rs, $cache_id);
        }

        return $this;
    }

    public function getByCompanyIdArray($id)
    {
        $hotlf = new HierarchyObjectTypeListFactory();
        $hotlf->getByCompanyId($id);

        $object_types = array();
        foreach ($hotlf as $object_type) {
            $object_types[] = $object_type->getObjectType();
        }

        return $object_types;
    }

    public function getByCompanyId($id, $limit = null, $page = null, $where = null, $order = null)
    {
        if ($id == '') {
            return false;
        }

        $strict_order = true;
        if ($order == null) {
            //$order = array('b.last_name' => 'asc');
            $strict_order = false;
        }

        $hcf = new HierarchyControlFactory();

        $ph = array(
            'id' => (int)$id,
        );


        $query = '
					select	*
					from	' . $this->getTable() . ' as a,
							' . $hcf->getTable() . ' as b

					where	a.hierarchy_control_id = b.id
						AND b.company_id = ?
						AND b.deleted = 0
				';
        $query .= $this->getWhereSQL($where);
        $query .= $this->getSortSQL($order, $strict_order);

        $this->ExecuteSQL($query, $ph, $limit, $page);

        return $this;
    }
}
