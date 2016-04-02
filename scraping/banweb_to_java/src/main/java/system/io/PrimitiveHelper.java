package main.java.system.io;

import java.util.Arrays;
import java.util.HashSet;

/**
 * For aggregate methods on Java primitives.
 */
public class PrimitiveHelper {
	
	/** The set of all primitive object classes, including String */
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
