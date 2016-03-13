package structure;

import java.util.HashMap;
import java.util.Map;

/**
 * Holds the semester data.
 */
public class Semester {
	/** Example 201610=Summer,2015 or 201630=Spring,2016 */
	String code = "";
	/** The map of semester indices to names. */
	public static final Map<Integer, String> semesterNames = new HashMap<>(3);
	
	static
	{
		semesterNames.put(10, "Fall");
		semesterNames.put(20, "Spring");
		semesterNames.put(30, "Summer");
	}
	
	public Semester(String code)
	{
		this.code = code;
	}
	
	/**
	 * @return The school year this semester is a part of (ending in the spring).
	 */
	public Integer getSchoolYear()
	{
		return new Integer(code.substring(0,4));
	}
	
	/**
	 * @return The school year this semester is a part of (ending in the spring).
	 */
	public Integer getCalendarYear()
	{
		Integer schoolYear = getSchoolYear();
		if (getSemesterIndex() < 20)
		{
			return schoolYear - 1;
		}
		return schoolYear;
	}
	
	public String getSemesterName()
	{
		return semesterNames.get(getSemesterIndex());
	}
	
	/**
	 * @return 10 for summer, 20 for spring, 30 for summer
	 */
	public Integer getSemesterIndex()
	{
		return new Integer(code.substring(4,6));
	}
}
