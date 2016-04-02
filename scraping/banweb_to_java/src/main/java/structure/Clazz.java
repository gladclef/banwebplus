package main.java.structure;

import java.util.LinkedHashMap;
import java.util.Map;
import java.util.TreeMap;

/**
 * Collector to hold all class data from scraped courses.
 */
public class Clazz {
	
	/**
	 * The available class attributes to scrape, plus subject.
	 */
	public static enum ClassAttribute
	{	
		CourseReferenceNumber("CRN", true),
		CourseShortName("Course", false),
		Campus("*Campus", false),
		Days("Days", false),
		Time("Time", false),
		Location("Location", false),
		Hours("Hrs", true),
		Title("Title", false),
		Instructor("Instructor", false),
		SeatsAvailable("Seats", true),
		SeatsLimit("Limit", true),
		EnrolledStudents("Enroll", true),
		Subject("subject", false);
		
		public String shortName = "";
		public Boolean interpretAsInteger = false;
		
		ClassAttribute(String shortName, Boolean interpretAsInteger)
		{
			this.shortName = shortName;
			this.interpretAsInteger = interpretAsInteger;
		}
	}
	
	/** Container for the class attributes to their values. */
	protected Map<ClassAttribute, Object> attributes = new LinkedHashMap<>();
	
	/**
	 * Adds the given attribute to this clazz.
	 * 
	 * @param attributeType The type of the attribute to add.
	 * @param value The value of the given attribute.
	 */
	public void addAttribute(ClassAttribute attributeType, String value)
	{
		if (attributeType.interpretAsInteger)
		{
			attributes.put(attributeType, new Integer(value));
		}
		else
		{
			attributes.put(attributeType, value);
		}
	}
	
	/**
	 * Creates a map containing the attributes (using their abbreviated names)
	 * to their values.
	 * 
	 * @return The newly created map of "abbreviated name" => value.
	 */
	public Map<String, Object> getAttributeValues()
	{
		Map<String, Object> retval = new TreeMap<>();
		
		for (ClassAttribute attribute : attributes.keySet())
		{
			retval.put(attribute.shortName, attributes.get(attribute));
		}
		
		return retval;
	}
}
