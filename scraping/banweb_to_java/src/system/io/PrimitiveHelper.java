package system.io;

import java.util.Arrays;
import java.util.HashSet;

public class PrimitiveHelper {
	@SuppressWarnings("rawtypes")
	public static final HashSet<Class> primitiveClasses;
	
	static
	{
		primitiveClasses = new HashSet<>(Arrays.asList(
			new Class[] {
				Boolean.class,
				Character.class,
				Byte.class,
				Short.class,
				Integer.class,
				Long.class,
				Float.class,
				Double.class,
				Void.class,
				String.class
			}
		));
	}
}
