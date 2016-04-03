package main.java.structure;

import java.util.HashMap;
import java.util.Map;

/**
 * Holds the semester data.
 */
public class Semester implements Comparable<Semester> {
	/** Example 201610=Summer,2015 or 201630=Spring,2016 */
	String code = "";
	/** The map of semester indices to names. */
	public static final Map<Integer, String> semesterNames = new HashMap<>(3);

	static {
		semesterNames.put(10, "Summer");
		semesterNames.put(20, "Fall");
		semesterNames.put(30, "Spring");
	}

	/**
	 * @param code
	 *            The banweb-style code for this semester.<br>
	 *            Example "201710" for Summer 2016.
	 */
	public Semester(String code) {
		this.code = code;
	}

	/**
	 * @return The banweb-style code for this semester.
	 */
	public String getCode() {
		return code;
	}

	/**
	 * @return The school year this semester is a part of (ending in the
	 *         spring).
	 */
	public Integer getSchoolYear() {
		return new Integer(code.substring(0, 4));
	}

	/**
	 * @return The school year this semester is a part of (ending in the
	 *         spring).
	 */
	public Integer getCalendarYear() {
		Integer schoolYear = getSchoolYear();
		if (getSemesterIndex() < 30) {
			return schoolYear - 1;
		}
		return schoolYear;
	}

	public String getSemesterName() {
		return semesterNames.get(getSemesterIndex());
	}

	/**
	 * @return 10 for summer, 20 for fall, 30 for spring
	 */
	public Integer getSemesterIndex() {
		return new Integer(code.substring(4, 6));
	}

	/**
	 * @return What should be the next semester, if the observed pattern
	 *         follows.
	 */
	public String getPredictedNext() {
		Integer semesterIndex = getSemesterIndex();
		if (semesterIndex == 30) {
			return (getSchoolYear() + 1) + "" + 10;
		}
		return getSchoolYear() + "" + (semesterIndex + 10);
	}

	@Override
	public int compareTo(Semester o) {
		Integer mySemester = new Integer(code);
		Integer otherSemester = new Integer(o.code);
		return mySemester.compareTo(otherSemester);
	}
	
	@Override
	public String toString() {
		return getSemesterName() + " " + getCalendarYear();
	}
}
