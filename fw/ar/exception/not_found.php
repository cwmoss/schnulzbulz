<?php

namespace xorc\ar\exception;

use RangeException;

class not_found extends RangeException {
}

/*
Exceptions

    ActiveRecordError - Generic error class and superclass of all other errors raised by Active Record.

    AdapterNotSpecified - The configuration hash used in ActiveRecord::Base.establish_connection didn’t include an :adapter key.

    AdapterNotFound - The :adapter key used in ActiveRecord::Base.establish_connection specified a non-existent adapter (or a bad spelling of an existing one).

    AssociationTypeMismatch - The object assigned to the association wasn’t of the type specified in the association definition.

    AttributeAssignmentError - An error occurred while doing a mass assignment through the ActiveRecord::Base#attributes= method. You can inspect the attribute property of the exception object to determine which attribute triggered the error.

    ConnectionNotEstablished - No connection has been established. Use ActiveRecord::Base.establish_connection before querying.

    MultiparameterAssignmentErrors - Collection of errors that occurred during a mass assignment using the ActiveRecord::Base#attributes= method. The errors property of this exception contains an array of AttributeAssignmentError objects that should be inspected to determine which attributes triggered the errors.

    RecordInvalid - raised by ActiveRecord::Base#save! and ActiveRecord::Base.create! when the record is invalid.

    RecordNotFound - No record responded to the ActiveRecord::Base.find method. Either the row with the given ID doesn’t exist or the row didn’t meet the additional restrictions. Some ActiveRecord::Base.find calls do not raise this exception to signal nothing was found, please check its documentation for further details.

    SerializationTypeMismatch - The serialized object wasn’t of the class specified as the second parameter.

    StatementInvalid - The database server rejected the SQL statement. The precise error is added in the message.

*/